<?php
session_start();

if (!isset($_SESSION['authenticated'])) {
    header("Location: login/index.php");
    exit();
}

$debugFile = __DIR__ . "/debug_log.txt";
if(!file_exists($debugFile)){
    echo "<h3 style='color:red'>❌ Debug log পাওয়া যায়নি!</h3>";
    exit();
}

echo "<div style='padding:20px;font-family:monospace;'>";
echo "<h3>📜 Debug Log</h3>";
echo "<div style='background:#f8f8f8;padding:10px;border:1px solid #ccc;
            max-height:600px;overflow:auto;font-size:14px;line-height:1.4;'>";

$lines = explode("\n", file_get_contents($debugFile));
foreach($lines as $l){
    $l = trim($l);
    if($l==='') continue;

    if(stripos($l,'NO MATCH')!==false){
        echo "<div style='color:#b30000;font-weight:bold;'>🚨 $l</div>";
    }
    elseif(stripos($l,'RAW:')!==false){
        echo "<div style='color:#555;'>$l</div>";
    }
    elseif(stripos($l,'DATE DETECTED')!==false){
        echo "<div style='color:#0066cc;'>$l</div>";
    }
    elseif(stripos($l,'ENTRY:')!==false){
        echo "<div style='color:green;'>$l</div>";
    }
    else{
        echo "<div>$l</div>";
    }
}

echo "</div>";
echo "<p><a href='index.php'>🔙 ফিরে যান</a></p>";
echo "</div>";
