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

namespace Hartenthaler\WebtreesModules\History\german-chancellors-and-presidents;

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
        return I18N::translate('German Chancellors and Presidents');
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
        return 'Hermann Hartenthaler';
    }

    /**
     * The version of this module.
     *
     * @return string
     */
    public function customModuleVersion(): string
    {
        return '2.0.3.2';
    }

    /**
     * A URL that will provide the latest version of this module.
     *
     * @return string
     */
        public function customModuleLatestVersionUrl(): string
    {
        return 'https://github.com/hartenthaler/german-chancellors-and-presidents/master/latest-version.txt';
    }

    /**
     * Where to get support for this module.  Perhaps a github respository?
     *
     * @return string
     */
    public function customModuleSupportUrl(): string
    {
        return 'https://github.com/hartenthaler/german-chancellors-and-presidents/';
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
     * @return Collection<string>
     */
    
    public function historicEventsAll(): Collection
    {
        return new Collection([
// Bundeskanzler:
        "1 EVEN Konrad Adenauer (CDU)\n2 TYPE Bundeskanzler von Deutschland\n2 DATE FROM 15 SEP 1949 TO 16 OCT 1963",
        "1 EVEN Ludwig Erhard (CDU?)\n2 TYPE Bundeskanzler von Deutschland\n2 DATE FROM 16 OCT 1963 TO 1 DEC 1966",
        "1 EVEN Kurt Georg Kiesinger (CDU)\n2 TYPE Bundeskanzler von Deutschland\n2 DATE FROM 1 DEC 1966 TO 21 OCT 1969",
        "1 EVEN Willy Brandt (SPD)\n2 TYPE Bundeskanzler von Deutschland\n2 DATE FROM 21 OCT 1969 TO 7 MAY 1974",
        "1 EVEN Walter Scheel (FDP)\n2 TYPE Bundeskanzler von Deutschland (nur geschäftsführend)\n2 DATE FROM 7 MAY 1974 TO 16 MAY 1974",
        "1 EVEN Helmut Schmidt (SPD)\n2 TYPE Bundeskanzler von Deutschland\n2 DATE FROM 16 MAY 1974 TO 1 OCT 1982",
        "1 EVEN Helmut Kohl (CDU)\n2 TYPE Bundeskanzler von Deutschland\n2 DATE FROM 1 OCT 1982 TO 27 OCT 1998",
        "1 EVEN Gerhard Schröder (SPD)\n2 TYPE Bundeskanzler von Deutschland\n2 DATE FROM 27 OCT 1998 TO 22 NOV 2005",
        "1 EVEN Angela Merkel (CDU)\n2 TYPE Bundeskanzler von Deutschland\n2 DATE FROM 22 NOV 2005",  
// Bundespräsidenten:            
        "1 EVEN Theodor Heuss (FDP)\n2 TYPE Bundespräsident von Deutschland\n2 DATE FROM 12 SEP 1949 TO 12 SEP 1959",
        "1 EVEN Heinrich Lübke (CDU)\n2 TYPE Bundespräsident von Deutschland\n2 DATE FROM 13 SEP 1959 TO 30 JUN 1969",
        "1 EVEN Gustav Heinemann (SPD)\n2 TYPE Bundespräsident von Deutschland\n2 DATE FROM 1 JUL 1969 TO 30 JUN 1974",
        "1 EVEN Walter Scheel (FDP)\n2 TYPE Bundespräsident von Deutschland\n2 DATE FROM 1 JUL 1974 TO 30 JUN 1979",
        "1 EVEN Karl Carstens (CDU)\n2 TYPE Bundespräsident von Deutschland\n2 DATE FROM 1 JUL 1979 TO 30 JUN 1984",
        "1 EVEN Richard von Weizsäcker (CDU)\n2 TYPE Bundespräsident von Deutschland\n2 DATE FROM 1 JUL 1984 TO 30 JUN 1994",
        "1 EVEN Roman Herzog (CDU)\n2 TYPE Bundespräsident von Deutschland\n2 DATE FROM 1 JUL 1994 TO 30 JUN 1999",
        "1 EVEN Johannes Rau (SPD)\n2 TYPE Bundespräsident von Deutschland\n2 DATE FROM 1 JUL 1999 TO 30 JUN 2004",
        "1 EVEN Horst Köhler (CDU)\n2 TYPE Bundespräsident von Deutschland\n2 DATE FROM 1 JUL 2004 TO 31 MAY 2010",
        "1 EVEN Christian Wulff (CDU)\n2 TYPE Bundespräsident von Deutschland\n2 DATE FROM 30 JUN 2010 TO 17 FEB 2012",
        "1 EVEN Joachim Gauck (parteilos)\n2 TYPE Bundespräsident von Deutschland\n2 DATE FROM 18 MAR 2012 TO 18 MAR 2017",
        "1 EVEN Frank-Walter Steinmeier (SPD)\n2 TYPE Bundespräsident von Deutschland\n2 DATE FROM 19 MAR 2017",
        ]);
    }
    
};
