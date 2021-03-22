<?php
require_once(__DIR__."/util.php");
require_once(__DIR__.'/../vendor/autoload.php');

/* 取得資料 */
/** @var resource $file 開啟CSV檔; "r" 只讀方式打開，將文件指針指向文件頭。; */
$file = fopen('https://data.nhi.gov.tw/Datasets/Download.ashx?rid=A21030000I-D50001-001&l=https://data.nhi.gov.tw/resource/mask/maskdata.csv', 'r');

/** @var bool $isHeader 是否為第一筆(表頭) */
$isHeader = true;

// 讀取CSV內容; 使用支援UTF-8的__fgetcsv函數，解決亂碼問題
while ($data = __fgetcsv($file)) {
    if($isHeader) {
        // 第一筆為表頭
        $header = $data;
        $isHeader = false;
        
    } else {
        // 取出資料所需欄位
        $row = [$header[1] => $data[1],     // 醫事機構名稱
                $header[2] => $data[2],     // 醫事機構地址
                $header[4] => $data[4]];    // 成人口罩剩餘數
        $records[] = $row;
    }
}

fclose($file);


/* 處理資料 */
/** @var string $searchKey 輸入參數 */
$searchKey = $argv[1];

/** @var array $result 過濾不符合搜尋條件的地址*/
$result = array_filter(
    $records, 
    fn($r) => strpos($r["醫事機構地址"], $searchKey) !== false
);

// 以口罩數量做排序
usort($result, 
      fn($r1, $r2) => $r2["成人口罩剩餘數"] - $r1["成人口罩剩餘數"]);

/** @var CLImate $climate  使用climate顯示表格 */
$climate = new League\CLImate\CLImate;
$climate->table($result);