<?php

/**
 * webtrees: online genealogy
 * Copyright (C) 2020 webtrees development team
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

use Fisharebest\Localization\Translation;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Module\AbstractModule;
use Fisharebest\Webtrees\Module\ModuleCustomInterface;
use Fisharebest\Webtrees\Module\ModuleCustomTrait;
use Fisharebest\Webtrees\Module\ModuleHistoricEventsTrait;
use Fisharebest\Webtrees\Module\ModuleHistoricEventsInterface;
use Illuminate\Support\Collection;

/** 
 * Historical facts (in German): Chancellors and Presidents of Germany (since 1949)
 * Historische Daten: Bundeskanzler und Bundespräsidenten der Bundesrepublik Deutschland (seit 1949)
 */
return new class extends AbstractModule implements ModuleCustomInterface, ModuleHistoricEventsInterface {
    use ModuleCustomTrait;
    use ModuleHistoricEventsTrait;

    public const CUSTOM_TITLE = 'German Chancellors Presidents 🇩🇪';

    public const CUSTOM_AUTHOR = 'Hermann Hartenthaler';
    
    public const CUSTOM_WEBSITE = 'https://github.com/hartenthaler/german-chancellors-presidents/';
    
    public const CUSTOM_VERSION = '2.0.5.0';

    public const CUSTOM_LAST = 'https://github.com/hartenthaler/german-chancellors-presidents/blob/master/latest-version.txt';

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
        return I18N::translate('Historical facts (in German) - Cancellors and Presidents of Germany (since 1949)');
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
     * Where to get support for this module.  Perhaps a github respository?
     *
     * @return string
     */
    public function customModuleSupportUrl(): string
    {
        return self::CUSTOM_WEBSITE;
    }

    /**
     * Should this module be enabled when it is first installed?
     *
     * @return bool
     */
    public function isEnabledByDefault(): bool
    {
        return false;
    }

    /**
     * Where does this module store its resources
     *
     * @return string
     */
    public function resourcesFolder(): string
    {
        return __DIR__ . '/resources/';
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
        switch ($language) {
            case 'de':
                // Arrays are preferred, and faster.
                // If your module uses .MO files, then you can convert them to arrays like this.
                return (new Translation(__DIR__ . '/resources/language/de.mo'))->asArray();
    
            default:
                return [];
        }
    }

    /**
     * All events provided by this module.
     * 
     * Each line is a GEDCOM style record to describe an event (EVEN), including newline chars (\n);
     *      1 EVEN <name> (<party>)
     *      2 TYPE <Chancellor|President> of Germany
     *      2 DATE <date period>
     *      2 NOTE [wikipedia de](<link>)
     *
     * markdown is used for NOTE; markdown should be enabled for your tree (see Control panel / Manage family trees / Preferences and then scroll down to "Text" and mark the option "markdown")
     */
    
    public function historicEventsAll(): Collection
    {
        $eventTypeC = I18N::translate('Chancellor of Germany');
        $eventTypeP = I18N::translate('President of Germany');
        $eventSubtypeA = I18N::translate('acting');
        
    /**
     * tbd: wikipedia should be selected based on the language of the webtrees user if the following pages exist in his wikipedia language version
     */
        $wikipedia  = "de";
        
        return new Collection([
// Chancellors:
        "1 EVEN Konrad Adenauer (CDU)\n2 TYPE ".$eventTypeC."\n2 DATE FROM 15 SEP 1949 TO 16 OCT 1963\n2 NOTE [wikipedia ".$wikipedia."](https://".$wikipedia.".wikipedia.org/wiki/Konrad_Adenauer)",
        "1 EVEN Ludwig Erhard (CDU?)\n2 TYPE ".$eventTypeC."\n2 DATE FROM 16 OCT 1963 TO 1 DEC 1966\n2 NOTE [wikipedia ".$wikipedia."](https://".$wikipedia.".wikipedia.org/wiki/Ludwig_Erhard)",
        "1 EVEN Kurt Georg Kiesinger (CDU)\n2 TYPE ".$eventTypeC."\n2 DATE FROM 1 DEC 1966 TO 21 OCT 1969\n2 NOTE [wikipedia ".$wikipedia."](https://".$wikipedia.".wikipedia.org/wiki/Kurt_Georg_Kiesinger)",
        "1 EVEN Willy Brandt (SPD)\n2 TYPE ".$eventTypeC."\n2 DATE FROM 21 OCT 1969 TO 7 MAY 1974\n2 NOTE [wikipedia ".$wikipedia."](https://".$wikipedia.".wikipedia.org/wiki/Willy_Brandt)",
        "1 EVEN Walter Scheel (FDP)\n2 TYPE ".$eventTypeC." (".$eventSubtypeA.")\n2 DATE FROM 7 MAY 1974 TO 16 MAY 1974\n2 NOTE [wikipedia ".$wikipedia."](https://".$wikipedia.".wikipedia.org/wiki/Walter_Scheel)",
        "1 EVEN Helmut Schmidt (SPD)\n2 TYPE ".$eventTypeC."\n2 DATE FROM 16 MAY 1974 TO 1 OCT 1982\n2 NOTE [wikipedia ".$wikipedia."](https://".$wikipedia.".wikipedia.org/wiki/Helmut_Schmidt)",
        "1 EVEN Helmut Kohl (CDU)\n2 TYPE ".$eventTypeC."\n2 DATE FROM 1 OCT 1982 TO 27 OCT 1998\n2 NOTE [wikipedia ".$wikipedia."](https://".$wikipedia.".wikipedia.org/wiki/Helmut_Kohl)",
        "1 EVEN Gerhard Schröder (SPD)\n2 TYPE ".$eventTypeC."\n2 DATE FROM 27 OCT 1998 TO 22 NOV 2005\n2 NOTE [wikipedia ".$wikipedia."](https://".$wikipedia.".wikipedia.org/wiki/Gerhard_Schröder)",
        "1 EVEN Angela Merkel (CDU)\n2 TYPE ".$eventTypeC."\n2 DATE FROM 22 NOV 2005\n2 NOTE [wikipedia ".$wikipedia."](https://".$wikipedia.".wikipedia.org/wiki/Angela_Merkel)",
// Presidents (without acting presidents):            
        "1 EVEN Theodor Heuss (FDP)\n2 TYPE ".$eventTypeP."\n2 DATE FROM 12 SEP 1949 TO 12 SEP 1959\n2 NOTE [wikipedia ".$wikipedia."](https://".$wikipedia.".wikipedia.org/wiki/Theodor_Heuss)",
        "1 EVEN Heinrich Lübke (CDU)\n2 TYPE ".$eventTypeP."\n2 DATE FROM 13 SEP 1959 TO 30 JUN 1969\n2 NOTE [wikipedia ".$wikipedia."](https://".$wikipedia.".wikipedia.org/wiki/Heinrich_Lübke)",
        "1 EVEN Gustav Heinemann (SPD)\n2 TYPE ".$eventTypeP."\n2 DATE FROM 1 JUL 1969 TO 30 JUN 1974\n2 NOTE [wikipedia ".$wikipedia."](https://".$wikipedia.".wikipedia.org/wiki/Gustav_Heinemann)",
        "1 EVEN Walter Scheel (FDP)\n2 TYPE ".$eventTypeP."\n2 DATE FROM 1 JUL 1974 TO 30 JUN 1979\n2 NOTE [wikipedia ".$wikipedia."](https://".$wikipedia.".wikipedia.org/wiki/Walter_Scheel )",
        "1 EVEN Karl Carstens (CDU)\n2 TYPE ".$eventTypeP."\n2 DATE FROM 1 JUL 1979 TO 30 JUN 1984\n2 NOTE [wikipedia ".$wikipedia."](https://".$wikipedia.".wikipedia.org/wiki/Karl_Carstens)",
        "1 EVEN Richard von Weizsäcker (CDU)\n2 TYPE ".$eventTypeP."\n2 DATE FROM 1 JUL 1984 TO 30 JUN 1994\n2 NOTE [wikipedia ".$wikipedia."](https://".$wikipedia.".wikipedia.org/wiki/Richard_von_Weizsäcker)",
        "1 EVEN Roman Herzog (CDU)\n2 TYPE ".$eventTypeP."\n2 DATE FROM 1 JUL 1994 TO 30 JUN 1999\n2 NOTE [wikipedia ".$wikipedia."](https://".$wikipedia.".wikipedia.org/wiki/Roman_Herzog)",
        "1 EVEN Johannes Rau (SPD)\n2 TYPE ".$eventTypeP."\n2 DATE FROM 1 JUL 1999 TO 30 JUN 2004\n2 NOTE [wikipedia ".$wikipedia."](https://".$wikipedia.".wikipedia.org/wiki/Johannes_Rau)",
        "1 EVEN Horst Köhler (CDU)\n2 TYPE ".$eventTypeP."\n2 DATE FROM 1 JUL 2004 TO 31 MAY 2010\n2 NOTE [wikipedia ".$wikipedia."](https://".$wikipedia.".wikipedia.org/wiki/Horst_Köhler)",
        "1 EVEN Christian Wulff (CDU)\n2 TYPE ".$eventTypeP."\n2 DATE FROM 30 JUN 2010 TO 17 FEB 2012\n2 NOTE [wikipedia ".$wikipedia."](https://".$wikipedia.".wikipedia.org/wiki/Christian_Wulff)",
        "1 EVEN Joachim Gauck (parteilos)\n2 TYPE ".$eventTypeP."\n2 DATE FROM 18 MAR 2012 TO 18 MAR 2017\n2 NOTE [wikipedia ".$wikipedia."](https://".$wikipedia.".wikipedia.org/wiki/Joachim_Gauck)",
        "1 EVEN Frank-Walter Steinmeier (SPD)\n2 TYPE ".$eventTypeP."\n2 DATE FROM 19 MAR 2017\n2 NOTE [wikipedia ".$wikipedia."](https://".$wikipedia.".wikipedia.org/wiki/Frank-Walter_Steinmeier)",
        ]);
    }
    
};
 