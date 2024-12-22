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

/**
 * tbd: add more images to historic data csv file
 * tbd: allow admin to select in the control panel to switch on/off Chancellors/Presidents individually;
 * tbd: add more countries like Austria and Switzerland
 * tbd: add a new column: image title
 * tbd: replace csv file by reading wikidata: https://www.wikidata.org/wiki/Q4970706 and P1308 (Amtsinhaber);
 *      wikidata has all the necessary information; maybe a general approach is possible for other countries, too.
 */

declare(strict_types=1);

namespace Hartenthaler\WebtreesModules\History\german_chancellors_and_presidents;

use Hartenthaler\Webtrees\Helpers\Functions;
use Fisharebest\Localization\Translation;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Module\AbstractModule;
use Fisharebest\Webtrees\Module\ModuleCustomInterface;
use Fisharebest\Webtrees\Module\ModuleCustomTrait;
use Fisharebest\Webtrees\Module\ModuleHistoricEventsTrait;
use Fisharebest\Webtrees\Module\ModuleHistoricEventsInterface;
use Fisharebest\Webtrees\Registry;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Collection;
use Psr\Http\Message\ResponseFactoryInterface;
use Fig\Http\Message\StatusCodeInterface;

use function substr;
use function file_exists;
use function preg_match_all;
use function strlen;

/** 
 * Historical facts (in German): Chancellors and Presidents of Germany (since 1949)
 * Historische Daten: Bundeskanzler und Bundespräsidenten der Bundesrepublik Deutschland (seit 1949)
 */
class GermanChancellorsPresidents extends AbstractModule implements ModuleCustomInterface, ModuleHistoricEventsInterface {
    use ModuleCustomTrait;
    use ModuleHistoricEventsTrait;

    //Title of custom module
    public const CUSTOM_TITLE      = 'German Chancellors Presidents 🇩🇪';

    //Name of custom module
    public const CUSTOM_MODULE     = 'german-chancellors-presidents';

    //Author of custom module
    public const CUSTOM_AUTHOR     = 'Hermann Hartenthaler';

    //Custom module version
    public const CUSTOM_VERSION    = '2.2.1.0';
    public const CUSTOM_LAST       = 'https://raw.githubusercontent.com/' . self::GITHUB_USER . '/' .
                                                self::CUSTOM_MODULE . '/master/latest-version.txt';
    //GitHub user name
    public const GITHUB_USER       = 'hartenthaler';

    //GitHub repository
    public const GITHUB_REPO       = self::GITHUB_USER . '/' . self::CUSTOM_MODULE;

    //GitHub API URL to get the information about the latest releases
    public const GITHUB_API_LATEST_VERSION  = 'https://api.github.com/repos/'. self::GITHUB_REPO . '/releases/latest';
    public const GITHUB_API_TAG_NAME_PREFIX = '"tag_name":"v';

    //GitHub website as information for admins
    public const CUSTOM_WEBSITE    = 'https://github.com/' . self::GITHUB_REPO . '/';

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
        return /* I18N: Description of this module */ I18N::translate('Historical facts (in German) - Chancellors and Presidents of Germany (since 1949)');
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
     * A URL that will provide the latest version of this module.
     *
     * @return string
     */
        public function customModuleLatestVersionUrl(): string
    {
        return self::CUSTOM_LAST;
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

                        if(!empty($matches[0]))
                        {
                            $version = $matches[0][0][0];
                            $version = substr($version, strlen(self::GITHUB_API_TAG_NAME_PREFIX));
                        }
                        else
                        {
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
        $lang_dir   = $this->resourcesFolder() . 'lang/';
        $file       = $lang_dir . $language . '.mo';
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
     */

    public function loadGermanChancellorsPresidents(): Collection
    {
        // translate types
        $eventTypeC = I18N::translate('Chancellor of Germany');
        $eventTypeP = I18N::translate('President of Germany');
        $eventSubtypeA = I18N::translate('acting');

        // path to CSV file
        $filePath = $this->resourcesFolder() . 'GermanChancellorsPresidents.csv';

        $collection = new Collection();

        // open file and read lines
        if (($handle = fopen($filePath, 'r')) !== false) {
            // ignore heading line
            $headers = fgetcsv($handle);

            // read CSV lines and process them
            while (($row = fgetcsv($handle)) !== false) {
                list($name, $type, $date, $article, $image, $attribution) = $row;

                // replace placeholders in column "type"
                $type = str_replace(['C', 'P', 'A'], [$eventTypeC, $eventTypeP, $eventSubtypeA], $type);

                // add line to Collection
                $collection->push([
                    'name' => $name,
                    'type' => $type,
                    'date' => $date,
                    'article' => $article,
                    'image' => $image,
                    'attribution' => $attribution,
                ]);
            }
            fclose($handle);
        }

        return $collection;
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
     * @param string $language_tag
     */
    
    public function historicEventsAll(string $language_tag): Collection
    {
    /**
     * tbd: wikipedia should be selected based on the $language_tag of the webtrees user if the article exists
     * in his wikipedia language version; but the article name depends maybe on the language, so it is complicated.
     */
        $wikipedia  = "de";

        // translate source (used for image attribution)
        $source = I18N::translate('source');

        // load data from csv file
        $eventsList = $this->loadGermanChancellorsPresidents();

        // generate GEDCOM records for events based on the data from the csv file
        $gedcomList = new Collection();
        foreach ($eventsList as $event) {
            $gedcomList->push(
                "1 EVEN ".$event['name'].
                "\n2 TYPE ".$event['type'].
                "\n2 DATE ".$event['date'].
                "\n2 NOTE ".    (($event['image'] == "") ?
                                    "[wikipedia ".$wikipedia."](https://".$wikipedia.".wikipedia.org/wiki/".
                                    $event['article'].
                                    " )"
                                :
                                    "[![wikipedia ".$wikipedia."](https://".$event['image']." )]".
                                    "(https://".$wikipedia.".wikipedia.org/wiki/".$event['article'].
                                    " )".
                                    (($event['attribution'] == "") ? "" : "\n3 CONT ".$source.": ".$event['attribution'])
                                )
            );
        }
        return $gedcomList;
    }
    
};
