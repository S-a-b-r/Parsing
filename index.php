<?php
error_reporting(E_ERROR);
require_once __DIR__."/phpQuery.php";

const HOST = "127.0.0.1";
const USER = "root";
const PASSWORD = "root";
const DBNAME= "parsing";
$neededStrings = [3, 4, 6];

$db = new PDO('mysql:host='.HOST.';dbname='.DBNAME, USER, PASSWORD);

$html = getHtml();
$pq = phpQuery::newDocument($html);

$rows = $pq->find(".gridRow");
$column = 0;

foreach ($rows as $row){
    $column += 1;
    if(in_array($column, $neededStrings)){
        $row = pq($row);
        $altColumns = $row->find('.gridAltColumn')->getString();

        $numTrade = trim($altColumns[0]);
        $numLot = trim($altColumns[1]);
        $cost = trim($altColumns[2]);
        $status = trim($altColumns[4]);
        $href = 'http://www.arbitat.ru'.$row->find('.gridAltColumn a')->attr('href');
        $dateTime = $row->find('.columnDateTime')->text();

        $costInDb = prepareToDbCost($cost);
        $dateTimeInDb = prepareToDbDateTime($dateTime);

        if(!isDataAlreadyExist($db, $numTrade, $numLot)){
            $sql = "INSERT INTO `parsed_data` (trade_num, lot_num, lot_href, cost, trade_datetime, status) VALUES (?, ?, ?, ?, ?, ?)";
            $statement = $db->prepare($sql);
            $statement->execute([$numTrade, $numLot, $href, $costInDb, $dateTimeInDb, $status]);
        }

        echo($numTrade . " " . $numLot. " " . $href . " " . $cost . " " . $dateTime . " " . $status . "\n");
    }
}

function getHtml()
{
    $headers = [
        "Accept: */*",
        "Accept-Language: ru,en;q=0.9,la;q=0.8",
        "Cache-Control: no-cache",
        "Connection: keep-alive",
        "Content-Type: application/x-www-form-urlencoded; charset=UTF-8",
        "Cookie: ASP.NET_SessionId=opdxka0opw5fofxr5nlzji0d;",
        "Origin: http://www.arbitat.ru",
        "Pragma: no-cache",
        "Referer: http://www.arbitat.ru/",
        "X-MicrosoftAjax: Delta=true",
        "X-Requested-With: XMLHttpRequest",
    ];
    $flow = curl_init("http://www.arbitat.ru");
    curl_setopt($flow, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($flow, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($flow, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/104.0.5112.124 YaBrowser/22.9.5.710 Yowser/2.5 Safari/537.36");
    curl_setopt($flow, CURLOPT_HEADER, true);
    curl_setopt($flow, CURLOPT_HTTPHEADER, $headers);
    return curl_exec($flow);
}

function isDataAlreadyExist($db, $numTrade, $numLot)
{
    $sql = "SELECT lot_num FROM `parsed_data` WHERE trade_num = ?";
    $statement = $db->prepare($sql);
    $statement->execute([$numTrade]);
    $lotsNums = $statement->fetchAll(PDO::FETCH_COLUMN);
    return in_array($numLot, $lotsNums);
}

function prepareToDbCost($cost):string
{
    $cost = preg_replace('/[^A-Za-z0-9,\-]/', '', $cost);
    return str_replace(',', '.', $cost);
}

function prepareToDbDateTime($dateTime):string
{
    $dateTimeInDb = preg_replace('/[^A-Za-z0-9.:\-]/', ' ', $dateTime);
    $dateTimeInDb = explode('  ', $dateTimeInDb);
    $dateInDb = explode(".", $dateTimeInDb[0]);
    $timeInDb = $dateTimeInDb[1];
    $dateTimeInDb = $dateInDb[2] . "." . $dateInDb[1] . "." . $dateInDb [0] . " " . $timeInDb;
    return $dateTimeInDb;
}

//Тут я пытался подключить скрипты
//$str = str_replace('src="/', 'src="http://www.arbitat.ru/', $html);
//$str = str_replace('href="/', 'href="http://www.arbitat.ru/', $str);
//$str = str_replace('http://www.arbitat.ru//', 'http://', $str);
//
//$str = $str. '<script>__doPostBack("ctl00$ctl00$MainContent$ContentPlaceHolderMiddle$PurchasesSearchResult$ctl01$ctl02", "")</script>';

//Тут я пытался POST запрос повторить
//$postParams = "ctl00%24ctl00%24BodyScripts%24BodyScripts%24scripts=ctl00%24ctl00%24MainContent%24ContentPlaceHolderMiddle%24UpdatePanel2%7Cctl00%24ctl00%24MainContent%24ContentPlaceHolderMiddle%24PurchasesSearchResult%24ctl01%24ctl05&ctl00%24ctl00%24LeftContentLogin%24ctl00%24Login1%24UserName=&ctl00%24ctl00%24LeftContentLogin%24ctl00%24Login1%24Password=&ctl00%24ctl00%24LeftContentSideMenu%24mSideMenu%24extAccordionMenu_AccordionExtender_ClientState=0&ctl00%24ctl00%24MainExpandableArea%24phExpandCollapse%24PurchasesSearchCriteria%24vPurchaseLot_lotNumber_%D0%BB%D0%BE%D1%82%D0%B0=&ctl00%24ctl00%24MainExpandableArea%24phExpandCollapse%24PurchasesSearchCriteria%24vPurchaseLot_purchaseNumber_%D1%82%D0%BE%D1%80%D0%B3%D0%B0=&ctl00%24ctl00%24MainExpandableArea%24phExpandCollapse%24PurchasesSearchCriteria%24vPurchaseLot_lotTitle_%D0%9D%D0%B0%D0%B8%D0%BC%D0%B5%D0%BD%D0%BE%D0%B2%D0%B0%D0%BD%D0%B8%D0%B5%D0%BB%D0%BE%D1%82%D0%B0=&ctl00%24ctl00%24MainExpandableArea%24phExpandCollapse%24PurchasesSearchCriteria%24vPurchaseLot_fullTitle_%D0%9D%D0%B0%D0%B8%D0%BC%D0%B5%D0%BD%D0%BE%D0%B2%D0%B0%D0%BD%D0%B8%D0%B5%D1%82%D0%BE%D1%80%D0%B3%D0%B0=&ctl00%24ctl00%24MainExpandableArea%24phExpandCollapse%24PurchasesSearchCriteria%24Party_contactName_AliasFullOrganizerTitle=&ctl00%24ctl00%24MainExpandableArea%24phExpandCollapse%24PurchasesSearchCriteria%24vPurchaseLot_InitialPrice_%D0%9D%D0%B0%D1%87%D0%B0%D0%BB%D1%8C%D0%BD%D0%B0%D1%8F%D1%86%D0%B5%D0%BD%D0%B0%D0%BE%D1%82%D1%80%D1%83%D0%B1=&ctl00%24ctl00%24MainExpandableArea%24phExpandCollapse%24PurchasesSearchCriteria%24Party_inn_%D0%98%D0%9D%D0%9D%D0%BE%D1%80%D0%B3%D0%B0%D0%BD%D0%B8%D0%B7%D0%B0%D1%82%D0%BE%D1%80%D0%B0=&ctl00%24ctl00%24MainExpandableArea%24phExpandCollapse%24PurchasesSearchCriteria%24vPurchaseLot_bargainTypeID_%D0%A2%D0%B8%D0%BF%D1%82%D0%BE%D1%80%D0%B3%D0%BE%D0%B2%24ddlBargainType=10%2C11%2C12%2C111%2C13&ctl00%24ctl00%24MainExpandableArea%24phExpandCollapse%24PurchasesSearchCriteria%24Party_kpp_%D0%9A%D0%9F%D0%9F%D0%BE%D1%80%D0%B3%D0%B0%D0%BD%D0%B8%D0%B7%D0%B0%D1%82%D0%BE%D1%80%D0%B0=&ctl00%24ctl00%24MainExpandableArea%24phExpandCollapse%24PurchasesSearchCriteria%24BargainType_PriceForm_%D0%A4%D0%BE%D1%80%D0%BC%D0%B0%D0%BF%D1%80%D0%B5%D0%B4%D1%81%D1%82%D0%B0%D0%B2%D0%BB%D0%B5%D0%BD%D0%B8%D1%8F%D0%BF%D1%80%D0%B5%D0%B4%D0%BB%D0%BE%D0%B6%D0%B5%D0%BD%D0%B8%D0%B9%D0%BE%D1%86%D0%B5%D0%BD%D0%B5=&ctl00%24ctl00%24MainExpandableArea%24phExpandCollapse%24PurchasesSearchCriteria%24Party_registeredAddress_%D0%90%D0%B4%D1%80%D0%B5%D1%81%D1%80%D0%B5%D0%B3%D0%B8%D1%81%D1%82%D1%80%D0%B0%D1%86%D0%B8%D0%B8%D0%BE%D1%80%D0%B3%D0%B0%D0%BD%D0%B8%D0%B7%D0%B0%D1%82%D0%BE%D1%80%D0%B0=&ctl00%24ctl00%24MainExpandableArea%24phExpandCollapse%24PurchasesSearchCriteria%24vPurchaseLot_purchaseStatusID_%D0%A1%D1%82%D0%B0%D1%82%D1%83%D1%81=&ctl00%24ctl00%24MainExpandableArea%24phExpandCollapse%24PurchasesSearchCriteria%24vPurchaseLot_BankruptName_%D0%94%D0%BE%D0%BB%D0%B6%D0%BD%D0%B8%D0%BA=&ctl00%24ctl00%24MainExpandableArea%24phExpandCollapse%24PurchasesSearchCriteria%24vPurchaseLot_BankruptINN_%D0%98%D0%9D%D0%9D%D0%B4%D0%BE%D0%BB%D0%B6%D0%BD%D0%B8%D0%BA%D0%B0=&__EVENTTARGET=ctl00%24ctl00%24MainContent%24ContentPlaceHolderMiddle%24PurchasesSearchResult%24ctl01%24ctl05&__EVENTARGUMENT=&__VIEWSTATE=&__SCROLLPOSITIONX=0&__SCROLLPOSITIONY=293&__EVENTVALIDATION=%2FwEdAC6vVXD1oYELeveMr0vHCmYPlMMwQdi9JE76XlDpiyGaGiK96Mh9z4MIAfI7yfbJUJsPO%2BW3lSX2%2FDWdCcCk3rJcyrcU%2BtH5HkUc0Yvcf5zJEfusfBu%2B8729k%2FlgDp402W3yZda1D3HpWSYwxVupBiDzxUqAwNrcAqPGyAKNeM4AtamXB8G46pQyE7Ou4F6qMTXxiiUsgg2FIm%2BqNKDvB7Tx1wsLz3uVDitIdW1lZLNUmyYlrA4KHjyQob8WN%2BOdgrOZQAlyEt7Ac1iMlWMve%2BMqqCrsOUlQmNy2NVlpKLTT57KnTrdrpaAhfRt5qtVLlLcHcNdJhcYI2VcxDA8VD3Ir%2Bjo7rW34WQv2uKzvBI7h4%2FwWuZwwD%2FJifOJ%2F2S9GcIEP5HNfaXqYRNVIFjH9SBQEfIdLlliO1q7GAES3rY12khyqpjgF%2FMMzeURfehtC2L3yuH1cazdw8ZXYvoCZFKEJkxgYYNqnMOtNk3HPQvvBt65D1CQGstEygXOM0M%2FFOnLy3Z54y7iTmQxHO3PYXpCliUuG6TxIJ6wljZxNYMaUpFHepmMBSblWiF0hlPGAW06Gop9QsQineJGwASs3%2BqzSqRHJKjtTI%2BEvssx%2FUGGDQMLLYT6030Sdbs%2Fjbns%2BesNmKWVmHAscdxTaoJ1iXJPd1mgMvxtEvWPOp8%2BH5hp2uEqdpRZYka0e9HAecvAqjHHHGctzL2A0QUlkveF1yD%2Bxs3VEL%2BtyDD2I2qKykC%2FB4RoSiaxRjewt3vKD8rA0rdjWgnn3CFbkRmaUb7S0y6wn%2F5xKIctnAx3op92ZwSyib8TBr0toBdLCJQzJ7bsV7REiaW64Grk4DXQTXa1aiMAyCjeHuz2YkgjgoXS1Znq2SswbSzaLLvEl5rMHIacrVnClK99uVWJkVmsOAAKMmpYegqyXE%2Bftg4U%2B17fn5qnGQmsoO3JUsyYElzrSDhh9iLPcSV%2FbqObCqsFGwbQg%2BWt3yj1W%2FeJjzfRsFg72BsENm%2BEhTwSkQUnrentBVDXEKR4%3D&__CVIEWSTATE=zVrbcxvVGdeutLLkS%2BQQR6Rc5E1IgRAha2U7iYMTxpExcQmJwUmYdpjZWWuP5G1Wu8ruKo55oDjh0g4ZQplSMh0gQNunTmecgMHkxgxvnb6sHjrTTl%2BY0um0Ux7av4B%2B3zmru2Q7JC7I4%2BPdc77zne9%2BfufIX3ORiNApJYf2pFLDg4PDETXKZysNz0HbTVvea%2FlIr6pGuazKC5FIlI91HNdsbUYns1USYBkNxHoOmTmz6EwpOXLM0gVuoN83q6p8B85ijxsitXPUaID348x%2BrkIciIzu9HVyPlyoJ20RxSHHbGIhv9BAoTija5mBOllDdHE%2B1v2YZZnWk8S2YXlh9%2F33nU4lU6lHRPcN97K75H7kLpfOuEuiu%2BxeK50X3c%2FcG%2B7V0jn3E%2Fh72V3EMXg%2FDxL4qbh%2BYXtbFp8B%2FQtsPtB3ldWr04wHTVCsyHFF11TF0UzjccssFgQBjKQZMK%2BHjvccMdK6RgwH2swJAv0ROttf5ZRFui5566RD8mmzaDi8Pxr0PLY50JuUEpKUQElFac%2Fe4WS0bCaDzNkDpzQyNyBJyYG3OffXnho33GvuVXi6DsYAdW64l9Asn0Av9rH%2BZRFUXGSd%2BARa0w6wlVg6A49L7iVmOiQvnd8rAssbpTOiBMPw8IL7ofi3F38pSoMju3fFRbrqVfdjyvuK6L4N8655wlxOiOIf%2FaOF%2FaN2QTFE25nXyb5tWdNwHra158heSSqc3rbf%2FRWQXnevlM6WFoD79dKr7qdi6WxFLHBe6bXSz4D9p6L7W5DgJRTIvSy6FxLumwnU8HrpRSrZNRD4BZi6BOKChot06AzVc6m0UNEKl1gSYTWmzw3siIulV%2FBdhPFFscb0lE9ZSTThdZgEU0vnUNllkGbR%2FZRJhMZ8O%2BG%2Bn8BIoou6HzVYBFa%2BJrpvJdz3EnGRUizCXPARSg1kixjC16krmK2h5yUxmaTWFh8su2IHGoh6tXSu9FOYfxXoFlAknLsrKQ6OSPFBEP4y%2FCy7H5XOgnqU5AyI%2FArqAKxB2WWqSpPTRfc37vvwczExOoC%2B2z86UNjf06mW4xaiMzWUSI6giSRRSu4dHG4VncnUwIT7rjg%2Bbk6LNBUX3SsYmCik%2B3v3A%2Fei6L4Oq1%2BiCQgEYmoQ%2FEL1OlM6714Cb54XcY2zHITRzbEqR%2FolNBk1I9YGNAn6b4GyuE6V9lKm9BpG8xKEG6sZ58qudpfL6vN1yZkE9aURUdq9Nzm8uVn93SMDz4Ng1ZSBqrwLHAcML9Pw%2BBiX85IVIngZlQHNWaAyB7XIgyXmubq8QyNcpFmN%2FK7A0OsJ943EW8IKyZeiyfcuCzLK5hNqWJC3Gn00z97FH%2FHz37nv0BDGhHoJvQQUlzGCPr8qUqGh3oB8iyz9sPi01HVZHLUdyzRy9ZJlTN209t63J4kfVhXA%2BwtUGbQGJP%2Fr6CnoWsbCTd1MvbQE45DBlUj1uEO2XACJ3xkeTEmpVHLX8PDQnh1rWrvRFzRpW1aka6v5QWSOaJLN%2FQVzMI3GF0QkZHUGPQCBDYX1Ii1NS1AYz7BVKuS4yhWUB0sHq983cCpIATG%2FwOrKFZr5n3h2uoaErUyK0jEWy7SI0RpXelVEv38Ga1yi5QkUK71MfceK0wIWPbZnvgml4j0oFT%2BnIT4kjYyAnUVapsolQhpxP2wsJBsb0UjW22wbN8j%2BIMUnFWCRrYIOvvrYUYc%2FYFsNHCWnHWGj%2BwYGajWel1U%2BHGnc0LkySArSx%2FuzFPSE0rad1hXbFvoykDWWqYsQJ8W8cbiYnyFWLCBvnT7A89FQLGAoeSLkMo6eTG5n7ZOKZjx2GhRWFQBUYwB3thdmWUfa1HWlYJPtU0UrM6vYxJ4mCjylLc0hlqZsP1UeOGQ6sm46bD2Z1X53MRbOK6cPESPnzAq8JMXCpnGCzKvmnCE8qhQdM2%2BeIg%2BSU4A84qIzq9lxUZLi4gOThjOYeiAuGkVdr28feNjr3hHrpLwKFoAt4UdaVnxwa%2Fn1IEiuE%2BsbMt4hWsQpWoaYVXSbPKIyXNkfEgSC4K4%2FzPOeP7Gzw7M3dkNHp%2FD8epn2qOboRIYqgVCBoaTKLoh7ALN4f5cQSEFlUD1Q20LCk7dLwinFcuZlZK9knMMQV%2FKYrin2BFjxiJVTDCjdFpUahPIPo0yBdjIVbq9MmmHIrKKWkUn9pkHtxEspdpr4f0h0olCQoahdhJ%2F2EnEjKh9sJ9AF7vZKZJGcZkMHUcdUFdNGhk3rIwY%2FaVX%2BsIK4vIK6guT%2B1PAwO2StrzHrkuKAYpywigUWenSXqmxuNOLQv6F2Is2tq0iThw%2BXA7Bh0y0HXriFYFBXOr3ulnWcCh8C4Y11Eb7gPXtFvArrqcxSf%2FctF%2B3%2BnvUt1V2tSnV3uxBY4NbFjFkQbeViXWfZABw9hthJHKWM1js%2BXbQsYmTmK65%2FeX2EnjQ0R1P0KUvLMLnxDOYdLOCgBwVgicLMG1gQEGvRmBiCmBhrExND4KlxktHyir56VDy7hqhYO8OmuNhAjQsmrMQGveNoESy9rdKS4q5e7wZkXHGUA2bRUHMqkGZ5jud5fzTQKwQZgBMiUjIOISyloIU%2Fg7leYQOc984CDsYyCkd4QaAkdKB6owBwVwiyWTCyA0C1B2m9Q%2FASw754Oq%2BgX4wm8MNgTkWY2iLOeQiZD25byBxQYF%2FXjKPzBSLTUJkwrbwMqJrdaCyWBfSOrNXj6vmWon8K81hgLan8He3Et9a12E07ilO0J8dl9312GMe7nTJ66hDudC%2FQU8givUWpYvN%2BAa%2FJ6F0ke9zYCqTj4%2BhOXxfvi0T7YiEMnSfIvB31xfrSNL2nTcsB6THoNdMQ7j5VFgsAX6L8bDAQfweURGIZkKSKBTsePNupG1Ef5%2FP5voYP%2FsVPNw%2FNDyfM3LSZdRLPkJkELjuWycAacfE4zIKV9kmJVCKZSMbFdFGHVCH7DFJ0LAXSaIreDYCYR80TxNi3OzkEeDKTHBnKZNWh7KCA62xvzT5RkewQQIsAEEYPKvYsUfF1p2yDsuz5ztruU4peJPZR9OeW2v6ioZ0skuN09HvjWgavLxVr%2FgBYZKcMdpl1cIrf7%2FPfMz0PSCafwAgglM5OjFmWMo%2BMWg0eLM%2FmVhxFS4b92KAuXFiANoDvKy6IBEEZYi5vCzLeX4TkU8zqMD8UCgdhnPd%2BA4E23CoyIHXnIVNRJwBkm1aH58AucFDazBcUi1ghJJ6GhQIYXEFmMZ%2FP7xOErtDdZebMz2NzMKMy8aun9jxKlezwIT08hLAJo7ZUWfhtpunCphua3qBHE%2B7BZgM0PXwA6VZaFXl0w7GBvk0aWbNL1nKGCQTg2ZBsFqgFwK2ix%2BRx3ZxRdO05eoGdSFcncvetRHGEMQpHUJvAfyBF1kKO4d1BQ1KWfSF86w15zqIKUlV7w15XiFKEkMLX2%2BV1BjdBs2lM1805otaUTDvYh2RTDZWnt7uJf%2Fed0DxTm2RpVhRvSwbjCrFWzKv5ixpsqbyxlCO0xELR2tYw4HmPjo3NQL3KkGgjiT2hnSZqf3O3aaWLNgCIp4rEmt%2FSavwHpmZsahjAW4y7GvrMmR9D9tDo394wVFA0q9pFZpVTmmmJKxHhmSLaROBgAb6zodsiJ4saHKMaZbTBLne36Js0bEcBGzXTP0caDWSbECyeaVGkzQ3jDkQXcRrN5swR5QSU5aYlHIjCvoY%2BGut08%2BD4AMcFoCRxAZ%2Bfw0%2B8bZ1vYU8M4Z2rbQxPM2PlAdghfX8b%2BmmwE%2BqLNA%2B3ozmpJ8pbYuKw6UwaOItm5f2riXGU2g1puX6vKBwDBDw%2FjUChXBkOmrpKLEzF4Ba6tYKVwpjCncI%2FoJrclHHqywrv7dXhLeVG%2BDtwvBnrtWYofAlsVjNq81QsZ%2BGYxyPYD829tWAp0QiWhC9gmTUaubWgvvBWXGobNH3Srri0Oy7tiUsj8V2Im0fiIxzdTMrWoZbHmtTJ%2FRVWRuvXm4%2F7C3ShCalL%2FwwPaAi6me0oq%2FUQNHfVqTVTrc2T49yfYMYXHmdPvIehaYTzAdxPVt2acFcM5mmdCs9pxmDqUHpyvCPDSnVPXkbg51Vy0M0fCrXmWEPGDAGfn9AIjN10ZqAjevAB6%2BoxhFKQ8cgwgLqulga4awdwjTClQJt1j9k2yc%2Fo8yyouFBwgC7BGB2h5ZjaIQnNE3k7Y1q6NlPdwYZw%2F1rLDjaze7cynBneJY0MDpHknhEBffrQWpRfRfOu2GZ2wHYagLi4AhAXVWJnYr3eRPxHg0lDJad5f6z3uGY5RUWvfkn%2BB64%2FyPfFNrNj4zSt5rWjsTDOZ29dsQhKMQ7cLa0AWM9OfRn5VuB9uzrE9qGqgIh2ESb2yg7%2BkwS9%2FR2zgff6w34f4n5uPYH%2FOiF%2Fzvv9DiB%2FFGM15F9P0wb59%2Fi%2Fc6Aftw8u8N9bA%2F2crxbvNyB9fA1uRLVXqBUU21fYUOT9wBqTazNOoBtIUFYws5qyrAegp07VwWp8h6zkchbJAeybKBo0mLpllWQV8AZ%2BwRimiYiUIVkzMnpRJRFZhaiFF4aa7TAwVNQjhg6sYQdXVJCOvSn6nDJvHytAD%2BmToUxmCVQ%2FFdA5ATeCLOEKDL5DVj0dQAKmVFSedfI6njKgYI45jqXNFB1ix1p3TzGYbXekZxUjR9R7Rp8mDAfDOvtPyPIBJXNCM3ITGtHVbbWDUyCXdrqR5N7RcWYFqmTjKMdBKnIBWls5wL8cRb73tXHS0zUGxyLxYBu6sUZXrAh2Pb9Q7IKf768YIh4IVJF8kxfcj%2BFNqXdtSgHdxnLjE77CJFiDPs1ADXuEf8P0NavZGuwFo1SQf62ETT0bNDMoC%2BILIxfhn8BkbfZpA7mRSyf9BDZ8S0guWovkulSub6cvOrrTB9FHb0B7cpamjukOUwfvQWsGO3GwOgJTAyod6G76pqhlr%2FoNF6oZuaf%2BqwmxiQsKRf%2FbgZJvaPFNRpt%2B9ebVuSUDtDBmVe6t4LyckSaIJUXDnLOUgvelDB6tj2p50qhKbX%2FtMmILTi2NxhSJr7qwWC93WxmYQrEw5gitdsK2lpuVTc91mppwYKNoExrYTW7ZSZ3NkpNvw%2FM97e1P2gjW0K%2BqW3ghJsvlyzOv7EyZtoP7C2xHshzlhYnGby%2B8%2FW6793dKVzKEHbGe1FSo341fX8D%2BBrVGePw2fQsibKtldNQsPAlQxXvLA1t8jURU4Z7yvwO6y882%2F2fS%2FwA%3D&__ASYNCPOST=true&";


