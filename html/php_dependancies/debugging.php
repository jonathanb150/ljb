<?php
header('Cache-control: private');
header('Content-Type: application/octet-stream');
header('Content-Length: 716169120');
header('Content-Disposition: filename=test.mp4');
$link = file_get_contents("https://uptobox.com/api/link?token=7a18d32cf5a45b8fb3b224cf576101976w220&file_code=7uu61j9rpo5y");
$link = json_decode($link, true);
$link = $link['data']['dlLink'];
//readfile($link);

?>
<?php
function readfile_chunked($filename,$retbytes=true) {
   $chunksize = 1*(1024*1); // how many bytes per chunk
   $buffer = '';
   $cnt =0;
   // $handle = fopen($filename, 'rb');
   $handle = fopen($filename, 'rb');
   if ($handle === false) {
       return false;
   }
   while (!feof($handle)) {
       $buffer = fread($handle, $chunksize);
       echo $buffer;
       ob_flush();
       flush();
       if ($retbytes) {
           $cnt += strlen($buffer);
       }
   }
       $status = fclose($handle);
   if ($retbytes && $status) {
       return $cnt; // return num. bytes delivered like readfile() does.
   }
   return $status;

}
readfile_chunked($link, false);
?>