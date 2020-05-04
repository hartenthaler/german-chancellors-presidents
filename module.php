<?php

declare(strict_types=1);

namespace Hartenthaler\WebtreesModules\HistoryGerman;

use Fisharebest\Localization\Translation;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Module\AbstractModule;
use Fisharebest\Webtrees\Module\ModuleCustomInterface;
use Fisharebest\Webtrees\Module\ModuleCustomTrait;
use Fisharebest\Webtrees\Module\ModuleHistoricEventsTrait;
use Fisharebest\Webtrees\Module\ModuleHistoricEventsInterface;
use Illuminate\Support\Collection;

/** 
 * Historical Facts (in German): Bundeskanzler und Bundespräsidenten der Bundesrepublik Deutschland
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
        return 'buka';
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
     * Should this module be enabled when it is first installed?
     *
     * @return bool
     */
    public function isEnabledByDefault(): bool
    {
        return true;
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
        "1 EVEN Konrad Adenauer (CDU)\n2 TYPE Bundeskanzler der Bundesrepublik Deutschland\n2 DATE FROM 15 SEP 1949 TO 16 OCT 1963",
        "1 EVEN Ludwig Erhard (CDU?)\n2 TYPE Bundeskanzler der Bundesrepublik Deutschland\n2 DATE FROM 16 OCT 1963 TO 1 DEC 1966",
        "1 EVEN Kurt Georg Kiesinger (CDU)\n2 TYPE Bundeskanzler der Bundesrepublik Deutschland\n2 DATE FROM 1 DEC 1966 TO 21 OCT 1969",
        "1 EVEN Willy Brandt (SPD)\n2 TYPE Bundeskanzler der Bundesrepublik Deutschland\n2 DATE FROM 21 OCT 1969 TO 7 MAY 1974",
        "1 EVEN Walter Scheel (FDP)\n2 TYPE Bundeskanzler der Bundesrepublik Deutschland (nur geschäftsführend)\n2 DATE FROM 7 MAY 1974 TO 16 MAY 1974",
        "1 EVEN Helmut Schmidt (SPD)\n2 TYPE Bundeskanzler der Bundesrepublik Deutschland\n2 DATE FROM 16 MAY 1974 TO 1 OCT 1982",
        "1 EVEN Helmut Kohl (CDU)\n2 TYPE Bundeskanzler der Bundesrepublik Deutschland\n2 DATE FROM 1 OCT 1982 TO 27 OCT 1998",
        "1 EVEN Gerhard Schröder (SPD)\n2 TYPE Bundeskanzler der Bundesrepublik Deutschland\n2 DATE FROM 27 OCT 1998 TO 22 NOV 2005",
        "1 EVEN Angela Merkel (CDU)\n2 TYPE Bundeskanzler der Bundesrepublik Deutschland\n2 DATE FROM 22 NOV 2005",  
// Bundespräsidenten:            
        "1 EVEN Theodor Heuss (FDP)\n2 TYPE Bundespräsident der Bundesrepublik Deutschland\n2 DATE FROM 12 SEP 1949 TO 12 SEP 1959",
        "1 EVEN Heinrich Lübke (CDU)\n2 TYPE Bundespräsident der Bundesrepublik Deutschland\n2 DATE FROM 13 SEP 1959 TO 30 JUN 1969",
        "1 EVEN Gustav Heinemann (SPD)\n2 TYPE Bundespräsident der Bundesrepublik Deutschland\n2 DATE FROM 1 JUL 1969 TO 30 JUN 1974",
        "1 EVEN Walter Scheel (FDP)\n2 TYPE Bundespräsident der Bundesrepublik Deutschland\n2 DATE FROM 1 JUL 1974 TO 30 JUN 1979",
        "1 EVEN Karl Carstens (CDU)\n2 TYPE Bundespräsident der Bundesrepublik Deutschland\n2 DATE FROM 1 JUL 1979 TO 30 JUN 1984",
        "1 EVEN Richard von Weizsäcker (CDU)\n2 TYPE Bundespräsident der Bundesrepublik Deutschland\n2 DATE FROM 1 JUL 1984 TO 30 JUN 1994",
        "1 EVEN Roman Herzog (CDU)\n2 TYPE Bundespräsident der Bundesrepublik Deutschland\n2 DATE FROM 1 JUL 1994 TO 30 JUN 1999",
        "1 EVEN Johannes Rau (SPD)\n2 TYPE Bundespräsident der Bundesrepublik Deutschland\n2 DATE FROM 1 JUL 1999 TO 30 JUN 2004",
        "1 EVEN Horst Köhler (CDU)\n2 TYPE Bundespräsident der Bundesrepublik Deutschland\n2 DATE FROM 1 JUL 2004 TO 31 MAY 2010",
        "1 EVEN Christian Wulff (CDU)\n2 TYPE Bundespräsident der Bundesrepublik Deutschland\n2 DATE FROM 30 JUN 2010 TO 17 FEB 2012",
        "1 EVEN Joachim Gauck (parteilos)\n2 TYPE Bundespräsident der Bundesrepublik Deutschland\n2 DATE FROM 18 MAR 2012 TO 18 MAR 2017",
        "1 EVEN Frank-Walter Steinmeier (SPD)\n2 TYPE Bundespräsident der Bundesrepublik Deutschland\n2 DATE FROM 19 MAR 2017",
        ]);
    }
    
};
