<?php
$f = "/var/www/html/StefyNails/stefynails/app/Providers/AppServiceProvider.php";
$c = file_get_contents($f);
if(strpos($c, "forceScheme") === false){
   $insert = "public function boot(): void\n    {\n        if (env('APP_ENV') !== 'local') {\n            \URL::forceScheme('https');\n        }\n";
   $c = str_replace("public function boot(): void\n    {", $insert, $c);
   // Clean up if it was already modified differently
   $c = preg_replace('/public\s+function\s+boot\(\)\s*:\s*void\s*\{\s*\{\s*if/', "public function boot(): void\n    {\n        if", $c);
   file_put_contents($f, $c);
   echo "Patched successfully.\n";
} else {
   echo "Already patched.\n";
}
