<?php
/*
 * ABOUT.PHP
 * =========
 * Shows info about textbook.
 */
?>

<h1>O učebnici</h1>

<center>
    <table border="1px" cellspacing="0px" cellpadding="3px">
        <tr style="font-weight: bold; text-align: center">
            <td width="50%" bgcolor="#99ff99">Co učebnice umí</td>
            <td width="50%" bgcolor="#ffcccc">Co učebnice bude umět</td>
        </tr>

        <tr style="text-align: center">
            <td width="50%" bgcolor="#99ff99" valign="top">
                hodnocení odstavců<br>
                základní diskuze<br>
                příklady k lekcím<br>
                levelový systém<br>
                profilové stránky uživatelů<br>
                zasílání novinek emailem<br>
                žebříček uživatelů<br>
            </td>
            <td width="50%" bgcolor="#ffcccc" valign="top">
                statistiky pochopení odstavců<br>
                úprava / mazání diskuzních příspěvků<br>
            </td>
        </tr>
    </table>
</center>


<p>Předmět <b>Úvod do funkcionálního programování</b> má dlouhodobě nízkou úspěšnost. To je zapříčiněno několika faktory. Jednak jej mají zapsaný studenti prvního ročníku, kteří studium na informatice jdou <b>jen tak zkusit</b>, jednak jsou zde studenti, kteří si myslí, že jim bude ke zvládnutí tohoto předmětu stačít týden před zkouškou. Podle mě jsou zde další dvě skupiny studentů.</p>
<p>Ti první na začátku semestru přijdou na přednášku a vůbec nic z ní nepochopí. Stejně tak je to na prvních cvičeních. Otevřou skripta, jednou přečtou první dvě stránky a se slovy <b>To se nikdy nemám šanci naučit!</b> je odloží až do konce semestru, protože si vetknou myšlenku, že se to nikdy nemají šanci naučit.</p>
<p>Ti druzí si oproti prvním vezmou skripta do rukou opakovaně, ale stále dokola koukají na ty nesmyslné shluky písmen znázorňující definice funkcí a nic jim to neříká. Z vlastní zkušenosti vím, že na chápání látky tohoto předmětu je zapotřebí velká dávka abstraktního myšlení. Sám jsem po mnohanásobném čtení skript pochopil podstatu předmětu až v devátém týdnu semestru. Docílil jsem toho jednoduchým trikem – k většině funkcí a datových struktur jsem si vymyslel přirovnání k věcem z reálného života.</p>
<p>Řekl jsem si, že by mohlo nemalému počtu studentů zpříjemnit život, pokud bych se s nimi o tato přirovnání podělil. Několik studentů mě na podzim 2008 <a href="https://is.muni.cz/auth/cd/1433/podzim2008/IB015/7108895/">na fóru požádalo</a>, zda bych jim nezkusil alespoň něco z náplně předmětu vysvětlit. A tak jsem začal učit <b>krabičkovou metodou</b>. Podle předmětové ankety měla docela úspěch:</p>

<div class="quote">
    <b>
        „cvičení byly zábavné a docela jsem tvé vysvětlování chápal.“<br>
        „Paráda, mašinky a krabičky jsou dobrý nápad :-)“<br>
        „Líbil se mi krabičkový učební styl a vůbec celý výklad látky, byl jasný a srozumitelný.“
    </b>
</div>


<p>Fakulta informatiky má opravdu skvělé učitele a jejich přednášky bývají většinou poutavé a zábavné. Mnohdy se ale v oboru pohybují tak dlouho a tak důkladně, že některé informace považují za naprosto základní a domnívají se, že se s nimi člověk rodí. Vedle formálních skript, kde se dozvíte vše potřebné, se vám tímto dostává do ruky o něco neformálnější učební materiál, který nepředpokládá předchozí znalosti prakticky čehokoli. Kromě ovládání PC na uživatelské úrovni.</p>

<h2>Kdo jsem</h2>
<p>Jmenuju se Standa, je mi 21 let, původem ze severomoravské Bruntále. Funkcionálnímu programování jsem se začal věnovat v prvním semestru na FI. Mám odučeno asi 50 hodin ve čtyřech seminárních skupinách z podzimu 2009.</p>

<h2>Levely (průběžně obměňováno/doplňováno)</h2>

<center>
    <table border="1px" cellspacing="0px" cellpadding="3px">
        <tr>
            <th>Číslo</th>
            <th>Potřebné množství xp bodů</th>
            <th>Název levelu</th>
        </tr>
    <?php
    require_once "levels.php";

    $number = 1;
    foreach($levels as $level) {
        echo "<tr>";
        echo "<td>".$number."</td>";
        echo "<td>".$level[0]."</td>";
        echo "<td>".$level[1]."</td>";
        echo "</tr>";
        $number++;
    }
    ?>

    </table>
</center>