#!/usr/bin/php -q
<?php

echo ("\nfsyjcdump v1.0\n(c) Derrick Sobodash 2003\n");
set_time_limit(6000000);

// first string at 0xe52ca
print "Loading ROM into memory...\n";
$fd = fopen("fsyjc.bin", "rb");
$fddump = fread($fd, filesize("fsyjc.bin"));
fclose($fd);

print "Checking for pointers file...";
if (file_exists("fsyjc_pointers.txt")){
	print "found!\nLoading pointers...";
	$pt = fopen("fsyjc_pointers.txt", "rb");
	$ptdump = fread($pt, filesize("fsyjc_pointers.txt"));
	fclose($pt);
	$pointers = split("\n", $ptdump);
	unset($pt, $ptdump);
}
else $pointers = loc_ptr($fddump);

print "\nDumping strings for " . count($pointers) . " pointers...\n";
$output = "";

list($tblf0, $tblf1, $tblf2, $tblf3, $tblf4, $tblf5, $tblf6) = maketablearray();

for ($i=0; $i<count($pointers); $i++) {
	print "  Dumping string $i...";
	$pointer = hexdec(bin2hex(substr($fddump, hexdec($pointers[$i]), 4)));
	print " $pointer... ";
	$thisline = ""; $chrchr = "";
	while ($chrchr != chr(0xff)){
		$chrchr = substr($fddump, $pointer, 1); $pointer++;

		if($chrchr==chr(0xf0)){
			$bank = $tblf0;
			$chrchr = substr($fddump, $pointer, 1); $pointer++;
		}
		else if($chrchr==chr(0xf1)){
			$bank = $tblf1;
			$chrchr = substr($fddump, $pointer, 1); $pointer++;
		}
		else if($chrchr==chr(0xf2)){
			$bank = $tblf2;
			$chrchr = substr($fddump, $pointer, 1); $pointer++;
		}
		else if($chrchr==chr(0xf3)){
			$bank = $tblf3;
			$chrchr = substr($fddump, $pointer, 1); $pointer++;
		}
		else if($chrchr==chr(0xf4)){
			$bank = $tblf4;
			$chrchr = substr($fddump, $pointer, 1); $pointer++;
		}
		else if($chrchr==chr(0xf5)){
			$bank = $tblf5;
			$chrchr = substr($fddump, $pointer, 1); $pointer++;
		}
		else if($chrchr==chr(0xf6)){
			$bank = $tblf6;
			$chrchr = substr($fddump, $pointer, 1); $pointer++;
		}
		else if($chrchr==chr(0xff)){
			$chrchr = substr($fddump, $pointer, 1); $pointer++;
			if($chrchr==chr(0xff)) {
				$thisline .= "{clsr}\r\n";
				$chrchr = substr($fddump, $pointer, 1); $pointer++;
				$thisline .= "{" . str_pad(bin2hex($chrchr), 2, "0", STR_PAD_LEFT) . "}\r\n";
				$chrchr = substr($fddump, $pointer, 1); $pointer++;
			}
			else if($chrchr==chr(0x00))
				break;
		}
		if(isset($bank))
			$thisline .= $bank[hexdec(bin2hex($chrchr))];
		else
			$thisline .= "{" . str_pad(bin2hex($chrchr), 2, "0", STR_PAD_LEFT) . "}\r\n";
	}
	unset($bank);
	$output .= "{" . $pointers[$i] . "}\r\n$thisline{end}\r\n\r\n";
	print "done!\n";
}

$fo = fopen("fsyjc_script.txt", "w");
fputs($fo, $output);
fclose($fo);

print "\nAll done!\n";

function loc_ptr($fddump) {
	//$pointer = 0xe52ca;
	$pointer_ar = array(0x10d4d8, 0x11105c, 0x113aa2, 0x11581e, 0x116f60, 0x1193d6, 0x11afc2, 0x11d4c8, 0x11de2a);
	//$end = 0xf1d56;
	$end_ar = array(0x1107f3, 0x1133b4, 0x115365, 0x116abb, 0x118e10, 0x11aa02, 0x11ce66, 0x11daf5, 0x11e5db);
	
	$i=0;

	print "Locating string pointers...\n";

	for ($z=0; $z<9; $z++) {
		$pointer = $pointer_ar[$z];
		$end = $end_ar[$z];
		
		while ($pointer < $end) {
			if(strpos($fddump, pack("N", $pointer)) === FALSE) {
				$pointer++;
				$strings[$i] = strpos($fddump, pack("N", $pointer));
			}
			else {
				$strings[$i] = strpos($fddump, pack("N", $pointer));
			}
			$pointer = strpos($fddump, chr(0xff), $pointer) + 1;
			$i++;
			print "  Found pointer ". str_pad($i, 4, "0", STR_PAD_LEFT) . "...\n";
		}
	}
	
	$output = "";
	for ($i=0; $i<count($strings); $i++)
		if($strings[$i] != 0)
			$output .= dechex($strings[$i]) . "\n";
	
	$fo = fopen("fsyjc_pointers.txt", "w");
	fputs($fo, rtrim($output));
	fclose($fo);

	return ($strings);
}

function maketablearray() {
	// Bank 1
	$fd = fopen ("tbl/t0.txt", "rb");
	$fddump = fread ($fd, filesize ("tbl/t0.txt"));
	fclose ($fd);
	$k=0;
	for ($i = 0; $i < strlen($fddump); $i = $i+2) {
		$tblf0[$k] = substr($fddump, $i, 2);
		$k++;
	}
	
	// Bank 2
	$fd = fopen ("tbl/t1.txt", "rb");
	$fddump = fread ($fd, filesize ("tbl/t1.txt"));
	fclose ($fd);
	$k=0;
	for ($i = 0; $i < strlen($fddump); $i = $i+2) {
		$tblf1[$k] = substr($fddump, $i, 2);
		$k++;
	}
	
	// Bank 3
	$fd = fopen ("tbl/t2.txt", "rb");
	$fddump = fread ($fd, filesize ("tbl/t2.txt"));
	fclose ($fd);
	$k=0;
	for ($i = 0; $i < strlen($fddump); $i = $i+2) {
		$tblf2[$k] = substr($fddump, $i, 2);
		$k++;
	}
	
	// Bank 4
	$fd = fopen ("tbl/t3.txt", "rb");
	$fddump = fread ($fd, filesize ("tbl/t3.txt"));
	fclose ($fd);
	$k=0;
	for ($i = 0; $i < strlen($fddump); $i = $i+2) {
		$tblf3[$k] = substr($fddump, $i, 2);
		$k++;
	}
	
	// Bank 5
	$fd = fopen ("tbl/t4.txt", "rb");
	$fddump = fread ($fd, filesize ("tbl/t4.txt"));
	fclose ($fd);
	$k=0;
	for ($i = 0; $i < strlen($fddump); $i = $i+2) {
		$tblf4[$k] = substr($fddump, $i, 2);
		$k++;
	}
	
	// Bank 6
	$fd = fopen ("tbl/t5.txt", "rb");
	$fddump = fread ($fd, filesize ("tbl/t5.txt"));
	fclose ($fd);
	$k=0;
	for ($i = 0; $i < strlen($fddump); $i = $i+2) {
		$tblf5[$k] = substr($fddump, $i, 2);
		$k++;
	}
	
	// Bank 7
	$fd = fopen ("tbl/t6.txt", "rb");
	$fddump = fread ($fd, filesize ("tbl/t6.txt"));
	fclose ($fd);
	$k=0;
	for ($i = 0; $i < strlen($fddump); $i = $i+2) {
		$tblf6[$k] = substr($fddump, $i, 2);
		$k++;
	}
		
	return array ($tblf0, $tblf1, $tblf2, $tblf3, $tblf4, $tblf5, $tblf6);
}

?>