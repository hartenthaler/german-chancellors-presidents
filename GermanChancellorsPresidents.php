<?php

/**
 * webtrees: online genealogy application
 * Copyright (C) 2025 webtrees development team
 *                    <https://webtrees.net>
 *
 * Copyright (C) 2024 Jonathan Jaubart (idea how to add images to historic records,
 * see https://www.webtrees.net/index.php/forum/help-for-release-2-1-x/38907-add-images-to-historic-events-modules#105102)
 *
 * ExtendedImportExport (webtrees custom module): using a custom module in webtrees 2.1 and 2.2, get version from GitHub
 * Copyright (C) 2024 Markus Hemprich
 *                     <http://www.familienforschung-hemprich.de>
 *
 * Copyright (C) 2025 Hermann Hartenthaler
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types=1);

namespace Hartenthaler\WebtreesModules\History\german_chancellors_and_presidents;

use Hartenthaler\Webtrees\Helpers\Functions;
use DateTime;
use Fisharebest\Localization\Translation;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\FlashMessages;
use Fisharebest\Webtrees\Validator;
use Fisharebest\Webtrees\View;
use Fisharebest\Webtrees\Module\AbstractModule;
use Fisharebest\Webtrees\Module\ModuleCustomInterface;
use Fisharebest\Webtrees\Module\ModuleCustomTrait;
use Fisharebest\Webtrees\Module\ModuleHistoricEventsTrait;
use Fisharebest\Webtrees\Module\ModuleHistoricEventsInterface;
use Fisharebest\Webtrees\Module\ModuleConfigInterface;
use Fisharebest\Webtrees\Module\ModuleConfigTrait;
use Fisharebest\Webtrees\Registry;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Collection;
use Psr\Http\Message\ResponseFactoryInterface;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use function substr;
use function file_exists;
use function preg_match_all;
use function strlen;

/** 
 * Historical facts: Chancellors and Presidents of Germany (since 1949)
 * Historische Daten: Bundeskanzler und Bundespräsidenten der Bundesrepublik Deutschland, Staatsoberhäupter der DDR (seit 1949)
 */

/**
 * tbd: wikidata: if partyEndDate <= startActingDate don't show the party (see Joachim Gauck)
 * tbd: wikidata: sometimes a Wikidata Id is shown instead of a label. check wikidata for the reason for that
 * tbd: wikidata: add photo, title of photo, and source of photo (like it is available in the csv file))
 * tbd: admin can select in csv: switch on/off Chancellors/Presidents individually;
 * tbd: admin can select for wikidata which of the queries should be shown
 * tbd: add more countries like Austria and Switzerland
 * tbd: use SPARQL fabric like it is suggested in web application
 * tbd: fetch errors when reading from wikidata
 * tbd: wikidata: cache results for one day and use cache instead of calling wikidata again
 */

class GermanChancellorsPresidents extends AbstractModule
                                  implements ModuleCustomInterface, ModuleHistoricEventsInterface, ModuleConfigInterface
{
    use ModuleCustomTrait;
    use ModuleHistoricEventsTrait;
    use ModuleConfigTrait;

    // title of custom module
    public const CUSTOM_TITLE = 'German Chancellors Presidents 🇩🇪';

    // name of custom module
    public const CUSTOM_MODULE = 'german-chancellors-presidents';

    // name of custom module author
    public const CUSTOM_AUTHOR = 'Hermann Hartenthaler';

    // custom module version
    public const CUSTOM_VERSION = '2.2.1.2';

    // GitHub user name
    public const GITHUB_USER = 'hartenthaler';

    // GitHub repository
    public const GITHUB_REPO = self::GITHUB_USER . '/' . self::CUSTOM_MODULE;

    // GitHub API URL to get the information about the latest releases
    public const GITHUB_API_LATEST_VERSION = 'https://api.github.com/repos/' . self::GITHUB_REPO . '/releases/latest';
    public const GITHUB_API_TAG_NAME_PREFIX = '"tag_name":"v';

    // GitHub website as information for admins
    public const CUSTOM_WEBSITE = 'https://github.com/' . self::GITHUB_REPO . '/';

    /**
     * Constructor.  The constructor is called on *all* modules, even ones that are disabled.
     * This is a good place to load business logic ("services").  Type-hint the parameters and
     * they will be injected automatically.
     */
    public function __construct()
    {
        // NOTE:  If your module is dependent on any of the business logic ("services"),
        // then you would type-hint them in the constructor and let webtrees inject them
        // for you.  However, we can't use dependency injection on anonymous classes like
        // this one. For an example of this, see the example-server-configuration module.

        // use helper function in order to work with webtrees versions 2.1 and 2.2
        $response_factory = Functions::getFromContainer(ResponseFactoryInterface::class);
    }

    /**
     * Bootstrap.  This function is called on *enabled* modules.
     * It is a good place to register routes and views.
     *
     * @return void
     */
    public function boot(): void
    {
        // Register a namespace for our views.
        View::registerNamespace($this->name(), $this->resourcesFolder() . 'views/');
    }

    /**
     * How should this module be identified in the control panel, etc.?
     *
     * @return string
     */
    public function title(): string
    {
        return self::CUSTOM_TITLE;
    }

    /**
     * A sentence describing what this module does.
     *
     * @return string
     */
    public function description(): string
    {
        return /* I18N: Description of this module */ I18N::translate('Historical facts - Chancellors and Presidents of Germany (since 1949)');
    }

    /**
     * The person or organisation who created this module.
     *
     * @return string
     */
    public function customModuleAuthorName(): string
    {
        return self::CUSTOM_AUTHOR;
    }

    /**
     * The version of this module.
     *
     * @return string
     */
    public function customModuleVersion(): string
    {
        return self::CUSTOM_VERSION;
    }

    /**
     * Where to get support for this module. Perhaps a GitHub repository?
     *
     * @return string
     */
    public function customModuleSupportUrl(): string
    {
        return self::CUSTOM_WEBSITE;
    }

    /**
     * {@inheritDoc}
     *
     * @return string
     *
     * @see \ModuleCustomInterface::customModuleLatestVersion
     */
    public function customModuleLatestVersion(): string
    {
        // No update URL provided.
        if (self::GITHUB_API_LATEST_VERSION === '') {
            return $this->customModuleVersion();
        }
        return Registry::cache()->file()->remember(
            $this->name() . '-latest-version',
            function (): string {
                try {
                    $client = new Client(
                        [
                            'timeout' => 3,
                        ]
                    );

                    $response = $client->get(self::GITHUB_API_LATEST_VERSION);

                    if ($response->getStatusCode() === StatusCodeInterface::STATUS_OK) {
                        $content = $response->getBody()->getContents();
                        preg_match_all('/' . self::GITHUB_API_TAG_NAME_PREFIX . '\d+\.\d+\.\d+/', $content, $matches, PREG_OFFSET_CAPTURE);

                        if (!empty($matches[0])) {
                            $version = $matches[0][0][0];
                            $version = substr($version, strlen(self::GITHUB_API_TAG_NAME_PREFIX));
                        } else {
                            $version = $this->customModuleVersion();
                        }
                        return $version;
                    }
                } catch (GuzzleException $ex) {
                    // Can't connect to the server?
                }

                return $this->customModuleVersion();
            },
            86400
        );
    }

    /**
     * Should this module be enabled when it is first installed?
     *
     * @return bool
     */
    public function isEnabledByDefault(): bool
    {
        return true;
    }

    /**
     * Where does this module store its resources
     *
     * @return string
     */
    public function resourcesFolder(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR;
    }

    /**
     * Additional/updated translations.
     *
     * @param string $language
     *
     * @return string[]
     */
    public function customTranslations(string $language): array
    {
        $lang_dir = $this->resourcesFolder() . 'lang/';
        $file = $lang_dir . $language . '.mo';
        if (file_exists($file)) {
            return (new Translation($file))->asArray();
        } else {
            return [];
        }
    }

    /**
     * Load list of historic events from csv file
     *
     * There is a heading line as comment.
     * Each following line has the same structure (columns separated by comma)
     * - name: <name> of person (<party>) (for example: "Konrad Adenauer (CDU)")
     * - type: using "C" for "Chancellor of Germany"; "P" for "President of Germany", and "A" for "acting" (for example "C (A)")
     * - date: acting <date period> (following GEDCOM syntax; for example: "FROM 15 SEP 1949 TO 16 OCT 1963")
     * - article: <article name> in wikipedia (for example: "Konrad_Adenauer")
     * - image: link to wikimedia commons: <link image> (without https, which is mandatory; for example: "upload.wikimedia.org/wikipedia/commons/thumb/8/86/Bundesarchiv_B_145_Bild-F078072-0004%2C_Konrad_Adenauer.jpg/220px-Bundesarchiv_B_145_Bild-F078072-0004%2C_Konrad_Adenauer.jpg")
     * - attribution: attribution to image from Wikimedia Commons (like "Bundesarchiv, B 145 Bild-F078072-0004 / Katherine Young / CC BY-SA 3.0 DE, CC BY-SA 3.0 DE <https://creativecommons.org/licenses/by-sa/3.0/de/deed.en>, via Wikimedia Commons")
     *
     * @return Collection each line in the file results in one entry
     */
    public function loadGermanChancellorsPresidents(): Collection
    {
        // path to CSV file
        $filePath = $this->resourcesFolder() . 'data/' . 'GermanChancellorsPresidents.csv';

        $collection = new Collection();

        // open file and read lines
        if (($handle = fopen($filePath, 'r')) !== false) {
            // ignore heading line
            $headers = fgetcsv($handle);

            // read CSV lines and process them
            while (($row = fgetcsv($handle)) !== false) {
                // add entry to Collection
                $result = $this->processCsvLine($row);
                if (!empty($result)) $collection->push($result);
            }
            fclose($handle);
        }

        return $collection;
    }

    /**
     * Process a CSV row and transform it into a structured array.
     * Ignore a comment line starting with "#" (return empty array)
     *
     * @param array $row The CSV line as an array of values.
     * @return array Processed data from the CSV row.
     */
    function processCsvLine(array $row): array
    {
        // check if first character in first element of $row is "#" (comment line)
        if (isset($row[0]) && str_starts_with($row[0], '#')) {
            return [];
        }

        // translate types
        $eventTypeC = I18N::translate('Chancellor of Germany');
        $eventTypeP = I18N::translate('President of Germany');
        $eventSubtypeA = I18N::translate('acting');

        // Extract values from the row
        list($name, $type, $date, $article, $image, $attribution) = $row;

        // Replace placeholders in column "type"
        $type = str_replace(['C', 'P', 'A'], [$eventTypeC, $eventTypeP, $eventSubtypeA], $type);

        // Return the processed row as structured data
        return [
            'name' => $name,
            'type' => $type,
            'date' => $date,
            'article' => $article,
            'image' => $image,
            'attribution' => $attribution,
        ];
    }

    /**
     * Structure of events provided by this module:
     *
     * Each line is a GEDCOM style record to describe an event (EVEN), including newline chars (\n);
     *      1 EVEN <name> (<party>)
     *      2 TYPE <Chancellor|President> of Germany
     *      2 DATE <date period>
     *      2 NOTE [![wikipedia de](<link image> )](<article>)
     *      3 CONT <attribution>
     *
     * Markdown is used for NOTE:
     * Markdown should be enabled for your tree (see Control panel / Manage family trees / Preferences
     * and then scroll down to "Text" and mark the option "markdown");
     * if Markdown is disabled the links are still working (blank at the end is necessary), but the formatting isn't so nice;
     *
     * @param string $languageTag preferred language of user (e.g. "de" or "en-GB")
     * @throws \DateMalformedStringException
     */

    public function historicEventsAll(string $languageTag = "en"): Collection
    {
        $gedcomList = new Collection();

        if ($this->useCsv()) {
            /**
             * tbd: wikipedia in csv link to wikipedia article should be selected based on the $language_tag
             * of the webtrees user if the article exists in his wikipedia language version;
             * but the article name depends maybe on the language, so it is complicated.
             */
            $persons = $this->loadGermanChancellorsPresidentsCsv($languageTag);
            foreach ($persons as $person) {
                $gedcomList->push($person);
            }
        }

        if ($this->useWikidata()) {
            $persons = $this->loadGermanChancellorsPresidentsWikidata($languageTag);
            foreach ($persons as $person) {
                $gedcomList->push($person);
            }
        }

        return $gedcomList;
    }

    /**
     * Load list of historic events from csv file
     *
     * @param string $languageTag preferred language of user (e.g. "de" or "en-GB") (not used at the moment)
     * @return Collection
     */
    public function loadGermanChancellorsPresidentsCsv(string $languageTag): Collection
    {
        $wikipedia = "de"; // language of wikipedia; this can be changed but you have to check the csv file if the referenced articles exist under the same name in "your" wikipedia

        // translate source (used for image attribution)
        $source = I18N::translate('source');
        // load data from csv file
        $eventsList = $this->loadGermanChancellorsPresidents();

        // generate GEDCOM records for events based on the data from the csv file
        $gedcomList = new Collection();
        foreach ($eventsList as $event) {
            $gedcomList->push(
                "1 EVEN " . $event['name'] .
                "\n2 TYPE " . $event['type'] .
                "\n2 DATE " . $event['date'] .
                "\n2 NOTE " . (($event['image'] == "") ?
                    "[wikipedia " . $wikipedia . "](https://" . $wikipedia . ".wikipedia.org/wiki/" .
                    $event['article'] .
                    " )"
                    :
                    "[![wikipedia " . $wikipedia . "](https://" . $event['image'] . " )]" .
                    "(https://" . $wikipedia . ".wikipedia.org/wiki/" . $event['article'] .
                    " )" .
                    (($event['attribution'] == "") ? "" : "\n3 CONT " . $source . ": " . $event['attribution'])
                )
            );
        }
        return $gedcomList;
    }

    /**
     * Load list of historic events from wikidata
     *
     * @param string $languageTag preferred language of user (e.g. "de" or "en-GB")
     * @return Collection
     * @throws \DateMalformedStringException
     */
    public function loadGermanChancellorsPresidentsWikidata(string $languageTag): Collection
    {
        $eventTypeC = I18N::translate('Chancellor of Germany');     // Bundeskanzler der Bundesrepublik Deutschland
        $eventTypeP = I18N::translate('President of Germany');      // Bundeskanzler der Bundesrepublik Deutschland
        $eventTypeG = I18N::translate('Head of former state GDR');  // Staatsoberhaupt der DDR

        $wikipedia = substr($languageTag, 0, 2);                 // instead of "en-GB" use "en"

        $wikidataObjects =
            [
                ['Q4970706', 'P1308', $eventTypeC],
                ['Q25223', 'P1308', $eventTypeP],
                ['Q16957', 'P35', $eventTypeG],
            ];
        $collection = new Collection();

        foreach ($wikidataObjects as $wikidataObject) {
            $persons = $this->getOfficeHolders($wikidataObject, $wikipedia);

            // tbd: if there are multiple records for a person,
            // then delete all except the one with the latest startPartyDate or the one inside the acting time period
            foreach ($persons as $person) {
                // all dates in the query result are in ISO 8601 (e.g. "1937-03-19T00:00:00Z")
                $startActingDate = !empty($person->startActingDate) ? $this->formatToGedcomDate($person->startActingDate) : null;
                $endActingDate = !empty($person->endActingDate) ? $this->formatToGedcomDate($person->endActingDate) : null;

                $birthDate = !empty($person->birthDate) ? new DateTime($person->birthDate) : null;
                $deathDate = !empty($person->deathDate) ? new DateTime($person->deathDate) : null;
                $startPartyDate = !empty($person->startPartyDate) ? new DateTime($person->startPartyDate) : null;

                $collection->push(
                    "1 EVEN " .
                    (isset($person->officeHolderLabel) ? $person->officeHolderLabel : "") .
                    (isset($person->birthDate) || isset($person->deathDate) ? " (" : " ") .
                    (isset($person->birthDate) ? "*" . $birthDate->format('d.m.Y') : "") .
                    (isset($person->deathDate) ? ", †" . $deathDate->format('d.m.Y') : "") .
                    (isset($person->birthDate) || isset($person->deathDate) ? ")" : "") .
                    (isset($person->startPartyDate) || isset($person->partyShortLabel) ? " (" : "") .
                    (isset($person->startPartyDate) ? I18N::translate("from") . " " . $startPartyDate->format('d.m.Y') . " " : "") .
                    (isset($person->partyShortLabel) ? I18N::translate("member of party %s", $person->partyShortLabel) : "") .
                    (isset($person->startPartyDate) || isset($person->partyShortLabel) ? ")" : "") .
                    "\n2 TYPE " . $wikidataObject[2] .
                    "\n2 DATE FROM " . $startActingDate .
                    (isset($person->endActingDate) ? " TO " . $endActingDate : "") .
                    "\n2 NOTE [" . $person->wikiType . "](" . $person->article .
                    " )"
                );
                // tbd: photo, title and attribution of photo
            }
        }
        return $collection;
    }

    /**
     * Format an ISO 8601 date as GEDCOM date (e.g. convert "1937-03-19T00:00:00Z" to "19 MAR 1937")
     *
     * @param string $dateString in ISO 8601 format
     * @return string date in GEDCOM format
     */
    function formatToGedcomDate(string $dateString): string
    {
        $gedcomMonths = ["JAN", "FEB", "MAR", "APR", "MAY", "JUN", "JUL", "AUG", "SEP", "OCT", "NOV", "DEC"];

        $month = (int)substr($dateString, 5, 2);
        $day = substr($dateString, 8, 2);
        $year = substr($dateString, 0, 4);

        return $day . " " . $gedcomMonths[$month - 1] . " " . $year;
    }

    /**
     * Fetches the values of a property for a given Wikidata entity.
     *
     * @param array $wikidataObject containing 3 strings: ID of the Wikidata item (e.g. Q4970706 for "Chancellors of Bundesrepublik Deutschland"),
     *                              property to be searched (e.g. P1308 for "Amtsinhaber"), and translated type (e.g. "Chancellors of Germany")
     * @param string $wikipediaLanguage preferred wikipedia based on language of user (e.g. "de")
     * @return array list of office holders and related data or an empty array if none found.
     */
    function getOfficeHolders(array $wikidataObject, string $wikipediaLanguage): array
    {
        // construct the SPARQL query
        $query = $this->buildQuery($wikidataObject, $wikipediaLanguage);

        // fetch data from wikidata as JSON
        $response = $this->readWikidata($query);

        // decode JSON
        $data = json_decode($response, true);

        $resultArray = [];

        // get all JSON "bindings"
        foreach ($data['results']['bindings'] as $binding) {
            $entry = (object)[];                                // new object for every binding
            foreach ($binding as $key => $value) {
                $entry->$key = $value['value'];
            }
            $resultArray[] = $entry;                            // add object to array of results
        }
        return $this->removeDuplicates($resultArray);
    }

    /**
     * Build SPARQL query to fetch data from Wikidata.
     *
     * @param array $wikidataObject containing 3 strings: ID of the Wikidata item (e.g. Q4970706 for "Chancellors of Bundesrepublik Deutschland"),
     *                              property to be searched (e.g. P1308 for "Amtsinhaber"), and translated type (e.g. "Chancellors of Germany")
     * @param string $wikipediaLanguage preferred wikipedia based on language of user (e.g. "de")
     * @return array The list of office holders (labels) or an empty array if none found.
     */
    function buildQuery(array $wikidataObject, string $wikipediaLanguage): string
    {
        list($wikidataId, $property, $eventType) = $wikidataObject;
        return "
            SELECT ?officeHolderLabel ?startActingDate ?endActingDate ?birthDate ?deathDate ?partyShortLabel ?startPartyDate ?article WHERE {
                wd:$wikidataId p:$property ?statement.
                ?statement ps:$property ?officeHolder.
                OPTIONAL { ?statement pq:P580 ?startActingDate. }
                OPTIONAL { ?statement pq:P582 ?endActingDate. }
                OPTIONAL { ?officeHolder wdt:P569 ?birthDate. }
                OPTIONAL { ?officeHolder wdt:P570 ?deathDate. }
                OPTIONAL {
                    ?officeHolder p:P102 ?partyStatement.
                    ?partyStatement ps:P102 ?party.
                    OPTIONAL { ?partyStatement pq:P580 ?startPartyDate. }
                    OPTIONAL { ?party wdt:P1813 ?partyShortLabel. }
                }
                OPTIONAL {
                    ?article schema:about ?officeHolder;
                             schema:inLanguage '$wikipediaLanguage'.
                }
                OPTIONAL {
                    ?fallbackArticle schema:about ?officeHolder;
                                     schema:inLanguage 'en'.
                }
                BIND(COALESCE(?article, ?fallbackArticle) AS ?article)
                SERVICE wikibase:label { bd:serviceParam wikibase:language '$wikipediaLanguage,en'. }
            }
            ORDER BY DESC(?officeHolderLabel) DESC(?startPartyDate)
        ";
    }

    /**
     * Performs a request to the Wikidata API and fetches a JSON response.
     *
     * @param string $query the SPARQL query
     * @return string the API response as a JSON string
     */
    function readWikidata(string $query): string
    {
        // Encode the query for the API
        $url = "https://query.wikidata.org/sparql?format=json&query=" . urlencode($query);

        $client = new Client([
            'timeout' => 15,
        ]);

        $response = $client->get($url);
        return $response->getBody()->getContents();
    }

    /**
     * Remove duplicated records; prefer articles in wikipedia against wikinews, wikivoyage, wikiquote and
     * prefer records with startPartyDate <= endActingDate and inside those the most recent one
     *
     * @param array $records list of records as a result of the SPARQL query containing several records for each Office Holder person
     * @return array containing the preferred records if there are several of them
     */
    function removeDuplicates(array $records): array
    {
        $tempStructure = [];
        $uniqueRecords = [];

        // generate intermediate structure
        foreach ($records as $record) {
            $label = $record->officeHolderLabel;
            $wikiType = $this->extractWikiType($record->article);       // that is for example "pedia" or "news" or ...
            if (isset($record->startPartyDate) && isset($record->endActingDate)) {
                $priority = $this->getPriority($wikiType, $record->startPartyDate, $record->endActingDate);
            } elseif (isset($record->startPartyDate) && !isset($record->endActingDate)) {
                $priority = $this->getPriority($wikiType, $record->startPartyDate, null);
            } elseif (!isset($record->startActingDate) && isset($record->endActingDate)) {
                $priority = $this->getPriority($wikiType, null, $record->endActingDate);
            } else {
                $priority = $this->getPriority($wikiType, null, null);
            }

            // add record to an array using the same Label
            $tempStructure[$label][] = [
                'record' => $record,
                'wikiType' => 'wiki' . $wikiType,
                'priority' => $priority,
            ];
        }

        // Finde den Datensatz mit der höchsten Priorität pro Label
        foreach ($tempStructure as $label => $dataList) {
            // Standardmäßig ist der erste Datensatz der mit der höchsten Priorität
            $highestPriorityRecord = $dataList[0];

            // Iteriere durch alle Datensätze mit diesem Label, um die höchste Priorität zu finden
            foreach ($dataList as $data) {
                if ($data['priority'] > $highestPriorityRecord['priority']) {
                    $highestPriorityRecord = $data;
                }
            }

            $highestPriorityRecord['record']->wikiType = $highestPriorityRecord['wikiType']; // test .":".$highestPriorityRecord['priority'];
            // Füge den Datensatz mit der höchsten Priorität zu den eindeutigen Datensätzen hinzu
            $uniqueRecords[] = $highestPriorityRecord['record'];
        }

        return $uniqueRecords;
    }

    /**
     * Extract type of wiki based on link to that wiki.
     * use the part between ".wiki" and next following "."
     * example: if link is "https://de.wikiquote.org/wiki/Olaf_Scholz" the function will return "quote"
     *
     * @param string $link string of link
     * @return string extracted type like "wikipedia", "wikinews", etc. or empty string, if there was no match found
     */
    function extractWikiType(string $link): string {
        if (preg_match('#\.wiki([^.]+)\.#', $link, $matches)) {
            return $matches[1];
        }
        return '';
    }

    /**
     * Estimate priority of a record based on type of wiki and start date in a party and end date of acting date period.
     *
     * @param string $wikiType contains "pedia" or "quote" or any other type
     * @param string $startPartyDate date for start in a party, formatted as ISO 8601 (e.g. "1937-03-19T00:00:00Z")
     * @param string $endActingDate date for end of acting time period, formatted as ISO 8601 (e.g. "1937-03-19T00:00:00Z")
     * @return int priority for that type
     */
    function getPriority(string $wikiType, ?string $startPartyDate, ?string $endActingDate): int {
        //
        $wikiPriority = [
            'pedia' => 1000,
            'quote' => 600,
            'news' => 500,
            'voyage' => 200,
        ];
        $priority = $wikiPriority[$wikiType] ?? 0;

        $startParty = !empty($startPartyDate) ? new DateTime($startPartyDate) : null;
        $endActing = !empty($endActingDate) ? new DateTime($endActingDate) : null;
        if (isset($startPartyDate) && isset($endActingDate)) {
            if ($startParty >= $endActing) {$priority = $priority - 10000;}
        }
        if (isset($startParty)) {
            $year = date("Y") - (int)$startParty->format('Y');        // number between 0 and about 125
            $priority = $priority - $year;                                          // prefer later dates
        }
        return $priority;
    }
    /**
     * generate list of preferences (control panel options)
     * there are more options like order of chapters or options to show or not to show a chapter
     *
     * @return array<int,string>
     */
    private function listOfPreferences(): array
    {
        return [
            'useCsv',
            'useWikidata',
           // 'showCsvChancellors',
          //  'showCsvPresidents',
        ];
    }

    /**
     * Open control panel page with options
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function getAdminAction(ServerRequestInterface $request): ResponseInterface
    {
        $this->layout = 'layouts/administration';
        return $this->viewResponse($this->name() . '::' . 'settings', $this->getInitializedOptions($request));
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @return array
     */
    private function getInitializedOptions(ServerRequestInterface $request): array
    {
        $response = [];

        $response['title'] = $this->title();
        $response['description'] = $this->description();

        $preferences = $this->listOfPreferences();
        foreach ($preferences as $preference) {
            $response[$preference] = $this->getPreference($preference);
        }
        $this->checkOptions($request, $response);

        return $response;
    }

    /**
     * check options and set default e.g. if the module is called the first time
     *
     * @param ServerRequestInterface $request
     * @param array $response
     */
    private function checkOptions(ServerRequestInterface $request, array & $response): void
    {
    }

    /**
     * Save the user preferences in the database
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function postAdminAction(ServerRequestInterface $request): ResponseInterface
    {
        if (Validator::parsedBody($request)->string('save') === '1') {
            $this->postAdminActionSave($request);
            FlashMessages::addMessage(I18N::translate('The preferences for the module “%s” have been updated.',
                $this->title()), 'success');
        }
        return redirect($this->getConfigLink());
    }

    /**
     * Save the user preferences for all parameters
     *
     * @param ServerRequestInterface $request
     */
    private function postAdminActionSave(ServerRequestInterface $request)
    {
        $preferences = $this->listOfPreferences();
        foreach ($preferences as $preference) {
            $this->setPreference($preference, trim(Validator::parsedBody($request)->string($preference)));
        }
        $this->postAdminActionChapter($request);
    }

    /**
     * save the user preferences for all parameters related to the chapters of this module in the database
     * order of chapters and status of chapters (enabled/disabled)
     *
     * @param ServerRequestInterface $request
     */
    private function postAdminActionChapter(ServerRequestInterface $request)
    {
        $params = (array) $request->getParsedBody();                    // tbd use Validator
        foreach ($params as $key => $value) {
            if (str_starts_with($key, 'status-')) {
                $this->setPreference($key, $value);
            }
        }
    }

    /**
     * Should content of csv file be shown?
     *
     * @return bool
     */
    private function useCsv(): bool
    {
        return ($this->getPreference('useCsv', '0') !== '0');
    }

    /**
     * Should data from wikidata be shown?
     *
     * @return bool
     */
    private function useWikidata(): bool
    {
        return ($this->getPreference('useWikidata', '0') !== '0');
    }
}