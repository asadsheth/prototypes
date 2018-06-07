<?php
// TODO BACKFILL WITH ROLE OF ALGO
// TODO ADD TOP REPLY TO EACH COMMENT
// TODO ADD THE OTHER TOP COMMENT TO COMPARE
// DESIGN newsroom stream designs: https://www.dropbox.com/s/fm8k7g5my8dqd5i/explore1.png?dl=0
// DESIGN newsroom stream designs (lighter): https://www.dropbox.com/s/sxf4hvnykvy8tcu/explore2.png?dl=0
// DESIGN web mobile cards: https://www.dropbox.com/s/fwyladpe3m8vr0p/stream_master_nov13.png?dl=0
error_reporting(0);
set_time_limit(3600);
ini_set('memory_limit', '1024M');
date_default_timezone_set('PDT');

// get our shared service for vibes
require('../shared/utilities.php');

// logger
$logp = fopen('log.log', 'a');
fwrite($logp, '----------------------------' . "\n");

// configs
$UPDATE_NEWSROOMISH = true; // update yo/newsroomish
$CACHE_DIR = './caches/'; // where to store jsons
$NUM_POSTS_TO_KEEP_PER_VIBE = 1000; // is this active?
$DEBUG = false; 
$ADDITIONAL_VIBE_PAGES = 0; // how many extra pages to get? roughly 10-15 per page
$COMMENTS_PER_POST = 0; // set this to 0 and the only comments we'll get are whitelisted ones
$REPLY_FETCH_QUALITY_THRESHOLD = 0.99; // set these reallllly high and we'll never go search for replies to comments
$REPLY_FETCH_COUNT_THRESHOLD = 100; // if there are less than this many replies we won't try to get em
// comments that these guys create are automatically whitelisted
$WHITELIST_COMMENTER_GUIDS = array(
	// '6NOU2PIONBDXJJKHMGGFXT4ZNE' => 1, // Rafi Sarussi
	// 'HRMMTS66W6MRK7JA2YM3LKR3DA' => 1, // Tenni Theurer
	// 'ET7XMWF2G3A3FTE3YEYZ2GAM7I' => 1,  // Cris Pierry
	// '5IKLEIEUE3X5RWTWGGUIQTRGBM' => 1, // Rory Brunner
	// '7NOEXPE67MEI2SW7E4AZ5VEN2Q' => 1, // Matt Romig
	// 'IQ3XSURBRCUA7KD33TYBJF7VOE' => 1, // Sid Saraf
	// 'EYIVQRJUGM4DOWSTFI4Z4Q4BUQ' => 1, // Johnny Rosenstein
	// '5CPX66OQUQPUSHGYFRPSD2MPWM' => 1, // Michelle Barnes
	// 'NRF5CZODGWRRSCVGB4ONX565FE' => 1, // David Okamoto
	// 'ACFWLBHSA45FWJLUDWRFLU3QRQ' => 1, // Joel Huerto
	// 'GONDC2SAZK2OZINYY3HIVELOQ4' => 1, // Andrew Chang
	// 'CC4BM7JUVBYG3E4LFMLW4HJLJI' => 1, // Caitlin Dickson
	// '2HG5UI5ZWG3HR5DTK747QH4RRY' => 1, // Lauren Johnston
	// 'I4LEPHFM42MTUHVWJJ7UP4MRBI' => 1, // Colin Campbell
	// '7UGHR4U4MLS54KJRFV2DKUSKOY' => 1, // Liz Goodwin
	// 'BLL4DNCC3CGMBGYUALCLJH2LBM' => 1, // Lisa Belkin
	// 'KLD7SJ2KP2H3GLZWPWLHE6UV4Y' => 1, // Kelli Grant
	// 'L57EL34EFQYRX674SQBC4TL6J4' => 1, // Charity Elder 
	// 'UIMNXFRYBAW7B2NHD4O6UWWN5M' => 1, // Matt Bai
	// 'NO63KMHEYMAEJY7NIJTGLHAB6U' => 1, // Garance Franke-Ruta
	// 'BJWZRZK3VQXT75HJUIKBECZ3WE' => 1, // Michael Isikoff
	// 'RIT3BY2UFCW6KTNNX5CD2FU5OU' => 1, // Hunter Walker
	// '4AU2B6Z3JMN3BDHGQ4ALD2OFEM' => 1, // Angela Kim
	// '73HBKCHBG6BS2FMUWAP4H3ARNM' => 1, // Kevin Kaduk
	// 'LWSA6PRJSGVLHXNC37QRDVIJ6E' => 1, // Jay Busbee 
	// 'XKTFNULYBR5XDRXQQRCXZJQSEA' => 1, // Mike Oz
	// 'GUJBKWQC3GTKLG55Q3IOMB4Y3E' => 1, // Dan Devine
	// 'AUILOSIHF7VOQFHONH5JPFHUZA' => 1, // Jason Klabacha
	// 'OBE22MVKMGKMEX6W5R2XGTC57A' => 1, // Sean Leahy
	// '2QISVDZWLZGHHDNGIETG53XY74' => 1, // Sam Cooper
	// 'O4QWVBLALY2Z4UYU4LHUGNL5AQ' => 1, // John Parker
	// 'O3CMS4JH2IN4TLCODEB7WD2FWI' => 1, // Nathan Giannini	
	
	// EIGENTRUST:
	// 'VW45KQSJEVPG7MAINWLC22ZIBI' => 1,
	// 'MPELFLOE644Z2ORWDHLD2N7PVY' => 1,
	// 'N2OV3CGEEYJE4N4LSHKWYD3YPU' => 1,
	// 'HAF7ZMFKZHJ6U265OH5BLNSVYE' => 1,
	// 'RBVBCQHUK7RHP3UJMHTVEG3OCQ' => 1,
	// 'YIZTFGVWXUWDOEKE7DK54MHSYY' => 1,
	// 'IZ3ZWV74RVJRUC4ZFWQGG5C4OE' => 1,
	// 'AQMGLSDEK7K76XIFMTB24O73KA' => 1,
	// 'JYEZJ6Y5IGLPACBA5E77G6PPLU' => 1,
	// 'EIYCZX32DA5QJSLIFANZVJIERE' => 1,
	// '7STZO3DSCAIHNCL73YFK23FDPU' => 1,
	// 'WMY4DZ3CCY5CTCEX3UAAW6OK2A' => 1,
	// 'TTT7R6S27GBXXXCXJD3SSXCNAY' => 1,
	// 'QVHYCZZVVHXSPRYQ6LMWJJRKGA' => 1,
	// 'N4HT6L3YZ2BZEWYCTGCDAFIL6M' => 1,
	// '2XBRIH2KTYNGBGWLA6HEFJ7DNM' => 1,
	// 'N45BOCFITVOCIGTPVAGOVRNF6Y' => 1,
	// 'IOUSRV5UATXWRTEMA4VCRREMYY' => 1,
	// 'VODJKID3YRZWZHZHHRJBTKJX3Q' => 1,
	// 'JPM2GUOZ3LTHRIXVUB6G7A5X7U' => 1,
	// ---- 

	// EIGENTRUST V2: 
	// 'VW45KQSJEVPG7MAINWLC22ZIBI' => 1,
	// 'IZ3ZWV74RVJRUC4ZFWQGG5C4OE' => 1,
	// 'XOR6OO5AIO7RWHIGBV4EEDKW6E' => 1,
	// 'XLIZK7N5FRFPNYISILP5W3I3MA' => 1,
	// 'T545P4IY74HGRU4GTIJJGQGIUU' => 1,
	// 'K2C6SQYOD5J7ZMA2QRO3PGLZEA' => 1,
	// '5MPIKLMY5LGNCWPLZW2HWRZ5EM' => 1,
	// 'VW5BW3GDKYDTP2TG27JB54MQEU' => 1,
	// 'F3B2743NRW5VWACYJVDCSZ4X7Y' => 1,
	// 'E7FV6MXLERWLYK6OXUIIVHTBGE' => 1,
	// 'EVFHDR4UAMXKEZSGBCXVG5DWJI' => 1,
	// 'I2AOGSDFA5YLQWA3NERNIJ4CUY' => 1,
	// 'PYPUGSGB7CC6J4IJE4SSZZ4QWY' => 1,
	// '7FTXUROSX72XP6TKLUMOVAB76Y' => 1,
	// 'VJDV2ZQVELNWGZ7XQYOVS747M4' => 1,
	// 'SNM7RGPG5DFCY7XVSQNAEQ6WPE' => 1,
	// '6RIKQPHT3HKUMKEW4ELJDK4D6M' => 1,
	// 'I7J6MUSVYOK3W66H4BBIVAE76A' => 1,
	// 'AXCSMSU4S6CSWI6MXOD2KOCF5U' => 1,

	// hafeez latest
	// 'DZ5BV4IZLMLPSMURTTJ7B4EFCE' => 1,
	// '3LPI6MRS2NXJWRESFO422DBUG4' => 1,
	// 'KL5IZJWMSYAK3L2ZHS7D6TJSZ4' => 1,
	// 'HMJ6VEOOOACZOFTXK2LFSP2LQ4' => 1,
	// '2RW6RTTFKNC2TFEWEWCCMUDJFY' => 1,
	// 'A7BCEWGGXJU65KPYRNUAZAJSKQ' => 1,
	// 'OY5LL6INJWDRLVZNCIJ7W4ZQ3A' => 1,
	// 'STJOBLWFE6FRJVDVTEWYDW4DTF' => 1,
	// 'BPAIUBJWKKSQANPMSHBOMOQU3M' => 1,
	// 'XWT6PX3DOK4O3NYD6SJD54CY2U' => 1,
	// '4QAIFTEJR2R5KABODP2KFEIKM4' => 1,
	// 'QTUBWH764WJA746A5IFBZ537DQ' => 1,
	// 'IXFXCIDSJQCPDAWPZQS5Z4SEIA' => 1,
	// 'J3MROBAIQXQ54X6Z55BENAKWCY' => 1,
	// '3PGTUCH4NPLBKT7LTYZ4RBKTWI' => 1,
	// '32WGBASLFNMKTWCNN7LKODOG74' => 1,
	// 'FILCW65OSOEC24KEV6DSHWXV5M' => 1,
	// 'ODAEE55K5NMWGNLJ6BCSDEGJEE' => 1,	
	// 'XILPOVTX6UHRJN4IOICNI5AOUQ' => 1,
	// '5RV3WY2TMMBEQTUBM3JE5L577U' => 1,
	// 'JTWQJZMMIGFRIB3XHKRK4ENTWT' => 1,
	// 'JZSOPBNWXJGVDXAPTH2UBYL7RA' => 1,
	// '75XB2B2RGHBRBNUVVYRLHTMQPE' => 1,
	// 'YB5U25Z6QIVBJZKCR4J4AOWPI4' => 1,
	// 'WG3RJQ3OUGZXFUQWJCBKHCXXV4' => 1,
	// '3HQKPAFNB6UG7TCRHOLOPF6CTE' => 1,
	// 'MB2Q2S65TS7JHDFMYJEHS4UTYE' => 1,
	// 'VEJHAAOPUGHUP6WQ4ZAZJYUXLY' => 1,
	// 'YBEBEIWGAEGMGUVYKDUYF6XLFQ' => 1,
	// 'NMNZ4DICZTOQVJB74DZI6QHCLA' => 1,
	// 'JHTBEPPKTIJFGG62YHFDWG2XAI' => 1,
	// 'MMNBOOLDL3S7WPFSBETYNYO3TA' => 1,
	// 'DMISCRTZDQGYITAE6JEGD4WH7U' => 1,
	// 'S7TTF5SQTSXPJ3DSPGJVCGXMLY' => 1,
	// 'PV6UFQ3KHYJZDGAIC3PDIWC4LM' => 1,
	// 'ZTKB6XAKWF6PHYGR53JXSY3T6Q' => 1,
	// 'SQRUFNHSCNBC4Z46NHC63IJ7Q4' => 1,
	// 'LP36H4TE7URBL3CSNEX4NU4IM4' => 1,
	// 'YILJ32527RLCR7HJTBBHRYFHQM' => 1,
	// 'VCGVVZRHKEX4ZHYGMCNCJTKDSQ' => 1,
	// 'ORKQ6XHYAFQ4BNLRWKLUHDBP4M' => 1,
	// '22KKDRBJWPO62JC5CG22WBD6FY' => 1,
	// 'EBSTDZW3XDD7MMVGZOAVDN2ETA' => 1,
	// 'CFZGKVMMYMFOIRO6V5TEC5RZMM' => 1,
	// 'LGSN2SZXGM765HWJHR625DQBCY' => 1,
	// 'CLSYVNNOLOKDGLMKS3FBZEFW2U' => 1,
	// '3BCNYTDOSZ75HHMHDWJTOZKKR4' => 1,
	// 'OFL75ZWTVNPRCSPKPN7H7L2IYU' => 1,
	// 'CSYSBELYOPJZM7ISF3XFJOFXFQ' => 1,
	// 'FHLHVZ6Q3FWUSJEALFO33OZWSQ' => 1,
	// 'EXO65VSW5NMKRA6V67P7H66ZRE' => 1,
	// 'QXPUXQ6G6FZIWUW47ZQA5J4KUM' => 1,
	// 'NUKQXLGWSJHL2KN5ZXKS5AEGOU' => 1,
	// 'RDU5Q3T7P7EA3R3WEUXPPFBHTQ' => 1,
	// 'JNLPW2567RY3TVNRYQSEFLXVAY' => 1,
	// '33NQ5XPUZITBLZTTKO33Q7PRKI' => 1,
	// 'TWKDD7M5UV43X3TRWJITXUJBO4' => 1,
	// 'TTGFIW2RX4AFEWCC6ICGJUOLLM' => 1,
	// 'CVBEIPCZFCGKBRO3FFUPNHGRTU' => 1,
	// 'UZYAJ5WRBGEH4HVJQYG3Z7UMZM' => 1,
	// 'PFVYRV2XHYEB5HKI7YM3SPEQ4E' => 1,
	// 'YLIMIEG4OGICM44NWOGSYMBLCU' => 1,
	// 'W5JX6Q3MPWENSY5F5ZQX45QDHM' => 1,
	// 'D53VAROIFSLH5OYBDAU6D7Q6BY' => 1,
	// '4AB3OTGUE5YTWZ3SXYUGOG23C4' => 1,
	// 'L26WVFKGCJFIP43ZLNYL6QO4PU' => 1,
	// '3T4VKCDXYECGOBBUNL7JNUUO4U' => 1,
	// 'WB2ECTQYC233AER7CQGX3WNZHY' => 1,
	// 'RM6X2JBSGUBLTKO4FWX6CT6I5Y' => 1,
	// 'I7VIQTWKI6J4WCEV3IZA6YBCJU' => 1,
	// 'WJ2ZFUZSMR6AJQVTEZFV3ANAJI' => 1,
	// '5ZHGAEK4K27UZAGNWJ52EW4JR4' => 1,
	// 'NBCKBD5URR6JR2GSVI5VJDNZAU' => 1,
	// 'JVM32IVU6LQTXK76UFPIHI4NRM' => 1,
	// 'KQEGTMBQRHU4KBAQMUJCOI2J2I' => 1,
	// 'YDHVY2IQ56WUH4GB7L3CMC6DLE' => 1,	
	// 'HA4EVSLCTYLAB7ILZZBB22WMPU' => 1,
	// '3RFHF76E5OJIFMSUSMGK6OGK7Y' => 1,
	// 'KLR7Z7NDYAQ54RKOZOYSOFODMY' => 1,
	// 'KLKO3O75KNLWYP55LFFPEXDVFU' => 1,
	// '2AO2UZMPOOUUHOD3DIISRIVEZ4' => 1,
	// '7I6XBZQGAFDJNGC2N443CGDIBQ' => 1,
	// '3LJ5XV7WJQIZEUB5Q3DWD2GAUE' => 1,
	// '5KWR2ALZU7F54YANYALUHYBI4A' => 1,
	// 'LQDYNIHFMQWZWJQ6ALERKA7VN4' => 1,
	// 'W4W5CTIFEXBRRIG5WM4R7DBLDU' => 1,
	// '6ON2VPRKPI6E25EWQPLEJE57UU' => 1,
	// 'JYPE2DQBW37I5CDHSSBNCCTRQ4' => 1,
	// 'QE66L6DG7Z5D235OB4DEBRSEFQ' => 1,
	// 'T5E2BCF654SRXEZBE22GXHGN6U' => 1,
	// 'O6JQ26N4XQTHI6TS5U2GLLSWEM' => 1,
	// '2TRUBNGZZD3C7ZLGSQINM3NYU4' => 1,
	// 'PIC5AI6FN7HOJQKZXZIY73OITI' => 1,
	// 'YFT6L5PEPNRSNDUEQ5QAJP23RA' => 1,
	// 'ZZKTJYR4EO3WYWGWYQ3PUGCU4Y' => 1,
	// '522IVCKGJFTSLBWBCGMZKN3VRA' => 1,
	// 'VESEKO3YFHRBRKN4NB2GZTVVAM' => 1,
	// 'Z2XMN5J2D4FCJG334PSJ2CKM3I' => 1,
	// '6LOYPCWSD7LPP2VJ6B5CSQDVRE' => 1,
	// 'PCLGAOZJU2UWMZU2AI3KXUFDIU' => 1,
	// 'OY3FQDTHCCJ7YGQMW3ZNEHDUJE' => 1,
	// 'AMEI7XZNGJ5EDBOCRDCASFV7JA' => 1,
	// '63BLE3YRHG67LXWSLDKP3WM4YE' => 1,
	// 'EXXCNOI3PVT64QJTXHAIYZHTMU' => 1,
	// 'GPRJN7YWFTJK6OMVA7L2MCYUAQ' => 1,
	// 'VOYEYJO5FLENIOTHAUCDLOII3E' => 1,
	// 'WCZHXH2SPP6IY6G5IQMA6RR53E' => 1,
	// 'L6FDHPIBRUHTDDIJJSFMT6HAFE' => 1,
	// 'ENXM3UUHOG7YDGWWJD7QXV3YLU' => 1,
	// 'XNSLQI7MZ2N2CCHNQ6KDARKGRE' => 1,
	// 'CKHCBH6FJ2GBBQUKV5KK37L6AI' => 1,
	// '4665C3INDR6UKSPRTSJMA3QNTI' => 1,
	// 'JRIBHUPB7JVUPFSALBZLFF5SUI' => 1,
	// '7RSBSAA3AHLSQW5PWM4VOK23NI' => 1,
	// 'DHZ2IPIA6OJFPNEB33Z6CEPOZQ' => 1,
	// 'IXN7ZRPFQJ4IWGTV3VLG6QCVG4' => 1,
	// 'LAZFGABH3A5S72POTQN4RP5EYY' => 1,
	// 'R6VOYYV7FGNYEL5ZRK5ELG3EKI' => 1,
	// 'WXDZ7YMIMKMIZTDJB66XAF7XPK' => 1,
	// 'IZOY4RYTVZKCIRW4IOGJRORIAE' => 1,
	// '7AK2X6GMFOZFCRSSJBXCOJRZUE' => 1,
	// 'E66ULZT3NB5J6I5XNPB3OKSWGI' => 1,
	// 'GEZ2ESUERPC26LZJU7YZPJMLLU' => 1,
	// 'YEEADPULQMGAZK73PVJWF23LFM' => 1,
	// '2L7LBZXOLXJZRAZUKVDPCSSTPE' => 1,
	// 'Y4BDZCDXEEZ63HUS5TEJLBIJFY' => 1,
	// 'VS5B6I6QRTMD6PGMUWCO2NPEJM' => 1,
	// 'W556I7VXZ4K5GNGDFKLM2S5XMA' => 1,
	// 'HWITKSTTNZQRYKW7RAY3IZTSAY' => 1,
	// 'EUFTSJPWETIQLDJ45QGH3DRWRA' => 1,
	// 'RQZK66J4MAVGSEO6BOKM7OTKIY' => 1,
	// 'JI4VC7ZN4HB2ISZC7SMUGPDEHA' => 1,
	// '2XJSEDEXDZ5YGRMF6YVIS2KCCI' => 1,
	// 'Y2RMNOZDNOAGOBZH5WKJRNFMQM' => 1,
	// 'O5EK4PMY7XZ5ERAHXTXV45LBVY' => 1,
	// '4LTGP6VAUDQ5HUFYB34ZTLSGH4' => 1,
	// '4VY6LQ4J233M7XLPBG2TA7ZXLM' => 1,
	// 'RWROT7UUVIDAC75X2CVLHMXT6A' => 1,
	// '3GJUJFA6O5LAME5DMLQBFA35Z4' => 1,
	// 'JYDWIDDFEXE4USKUFK3OR6ORRE' => 1,
	// 'D37RQHBKV7QYBXETUTMYSH76SA' => 1,
	// 'YGZRFKWQGMB63QL5N4INBS3FSA' => 1,
	// 'HFWLTNYZGHDSFP5XSQR7CRTSKQ' => 1,
	// 'XJAKJNAUTHSAH4WT5O5KHGEC4Q' => 1,
	// '6WQ44F4DMOKM4A7I3VTOKNBHCE' => 1,
	// 'TRN7JIFZTGU32OLWJIECDTL224' => 1,
	// 'ISPNRVXMQBW5GQCBVUMIIQ4NZE' => 1,
	// 'V4CBADLKVPCPEH5Z76XF4KQNLU' => 1,
	// 'KPYIPEHNVJDDNLI3WYBTQCGEPM' => 1,
	// 'ZXQYLYE35HN62PCFG26UUETIPI' => 1,
	// 'UJZ3266STWXRPYGQOKS235STNU' => 1,
	// 'PG4XYWMFYQNUVOXSLQHTY27P3I' => 1,
	// '6Y4GG7M6UWGVNOSM6GSKJNYMPM' => 1,
	// '2QV2Y5SIQIHMH4G7LZIFR44ZKU' => 1,
	// 'BZG2O4DG66SQSFITGF2IBPSRBU' => 1,
	// '7XG6YT2ULQXQSITUW6IG2374CQ' => 1,
	// 'EDIECQCAOJXZUNY2NHYNBGBMJI' => 1,
	// '7FJ6PEURQLWQHMOTHHFNBJTY7U' => 1,
	// 'NPW7GOTZBHLI5SWAAXBDRK7AR4' => 1,
	// 'MLKOOS5G5NZBECXS3X7SEE2JX4' => 1,
	// 'NDY3NT2JCEJUMDKTOTAOE6MHUA' => 1,
	// 'FDQQM4YOURYYZJOJUK57P5HXFQ' => 1,
	// 'L3YK7IWEQRV54VI3UOUPH4EBSQ' => 1,
	// 'QHMX3GNJFSF2YRBHQHXVQMBNVQ' => 1,
	// 'GY6HSOKHE45FIEWQ53S6QNW2IA' => 1,
	// 'VEL4ATKSRK3JRIWCTLWWPY6XYQ' => 1,
	// 'OJPRGL66VKZ6DRPUZBJ2XLMSFU' => 1,
	// 'DOMJKOSTJQWQOVUU43JNAVHOQE' => 1,
	// '43YNESSSTAJPQS7JGVRSPYHCG4' => 1,
	// 'VF6EWZ7MMW4KJ7Y44EB6DXBNPA' => 1,
	// 'FEEX35NOMISPELG3MXIQWXICKQ' => 1,
	// 'U7W6IIMH43LKK2BFWXPAOF6FKI' => 1,
	// 'ZSA64ZFM2Y67KBVNPBSHKXIWTQ' => 1,
	// '4U3DOTJXIBODL2HVDZ6N5A3EME' => 1,
	// '3YWNSHYSKMTFIOK4HBNVQ56ELI' => 1,
	// '2FIUDUHYLAEF2CU64A5FWMT3LY' => 1,
	// 'W6QF2AWLRSCN6K6GBIYLRRW7LY' => 1,
	// 'DDDAMBIMCS64TUZLKUF4SVW2SE' => 1,
	// 'UBXD6QVVD6XD2K3GOFOCHAB5SY' => 1,
	// 'XEOS5FJGULUVQTOKO5CESXKZ34' => 1,
	// 'QUMFEOJQWV4ZLZAFCKSFQABIZY' => 1,
	// 'NVGX6QWPLCKKQXCMSZNV2UT3CQ' => 1,
	// '5U5M53MLIWPN7P63NKW6EJAWPY' => 1,
	// 'WWVV3UWHT6S4FG74ISZZ2UKQUE' => 1,
	// 'KZFQJQN2WNPO3IXM7F6OBAP3EE' => 1,
	// 'YNIA72F6U24SZWRJ67C4RCO2VQ' => 1,
	// 'UPAK563MRYISGKDRFBRKSNQYI4' => 1,
	// 'XWNHVS6IECDCVYRLPOOGN5TUCQ' => 1,
	// 'BSV7TLZSUERLRZKLURI562ZSJQ' => 1,
	// 'X6DGAXQQEH56637CU72EMLYK2Y' => 1,
	// 'XERSQCO3I63SATDF5VTVL3R4YY' => 1,
	// 'HIH6JJGQLEAE2N6MR56VGC2RAU' => 1,
	// 'XS3YQABAGTYKGIDGR4DOUHRLWE' => 1,
	// '44OP42CT5IB3ZEAXYEJ3WQJULY' => 1,
	// 'LWFEMDDFBFRTRSG3OICMEY7YAI' => 1,
	// 'E2MZY7JMOWNRWP4GVA6TBMWD2A' => 1,
	// 'WMBYKS522FXOQ54UH53XKGDWHA' => 1,
	// 'UIIG6D3KQYJ7PMO2FR4DMHS66I' => 1,
	// 'GJSV3AMPJ5JDD7YEYIAWQTAP7Y' => 1,
	// 'QQN6XFVQVQLTC3RR66RQSXNRVQ' => 1,
	// 'I44JDQAQLMBXJZA2P7PL7T5VUY' => 1,
	// 'U7O27J3NIG3BAGHS5NU725XROA' => 1,
	// 'JGUJL5RZTEC2EQ27GVGFSL6LYY' => 1,
	// 'WESTHJLN5V7YAI4IICZSGUT4VM' => 1,
	// '7E52XQ3AWY6JTGM6RBUUP6XMMU' => 1,
	// 'THSYCPVBW4G3ZWFTI3CRNKBULI' => 1,
	// 'N7DQSGUDVRBNPP5N4BAYUUI66I' => 1,
	// 'FB6BKJU22DYV6A5ICC4PPX4LRY' => 1,
	// 'N73RAUJEK345GBEM2DSXZRVV67' => 1,
	// 'NTSVZBOULAUOTAZUPPBKLCISTE' => 1,
	// 'UQMORHIXAOUDIVXUQOQH4O2OOQ' => 1,
	// 'CKYWFEV55A3JROAM6JKQ6KROKU' => 1,
	// 'YM4BX7UVAVFQSHAH6ZYUFHBN4I' => 1,
	// 'DLFDG2JS2CWECMDMELZNILFBFI' => 1,
	// 'FEXW4MJDMWKTH244QTE55EDPPU' => 1,
	// 'ECETOF5DD5IF5GI7QNT3WR64EE' => 1,
	// 'GXIVFT7MXDI6KHDY3W46JN74JQ' => 1,
	// 'YBQGDKGRWFXTQHDKWSOMMLVQJQ' => 1,
	// '7JDEDZLBXHNI2V3OG4O7LZYBQE' => 1,
	// '3FDE7NQABSLDPFKCU36GF5HBYI' => 1,
	// 'NASHATHB5POIHE2X4UJHIM4FWE' => 1,
	// 'PYGIELSNXQ4JZ6XZ3AEYK2VE6I' => 1,
	// 'PDSCWFPQ2XOY3AEIIFPNTQBRI4' => 1,
	// 'JPSCZ4XMJ2JQ7RIJ63U3BPCZLM' => 1,
	// 'D5S3IZR3GE2OSKLFWIVQWMQSV4' => 1,
	// 'E2S7SUPEEAUL4WXSM56OM2GNRI' => 1,
	// 'TQR2OCXMEO7MKTYPDVGRJI4WVA' => 1,
	// 'OXHF7CLMESYE6SBZSPVENTPNEA' => 1,
	// '6YDZ4HF2O3X2362KKSJDZCYRCU' => 1,
	// 'J3IWRHTIE7MEBEYFNQ3KGC5B6A' => 1,
	// '6SQNXFKT2BXHTO6UN5OBJITJDI' => 1,
	// '6ECSSQGJEYO66O34HM64XOTHMM' => 1,
	// 'AI7BRXQJN2TXNG7I7OOG5YAU2M' => 1,
	// 'DJJWWFRJNZVOIAXE7ZCE7POKZU' => 1,
	// 'GBSUNLGBOTUF5EKHASRFOHM7AQ' => 1,
	// 'KPARCIPLV5IN762N5R2ALPQFBI' => 1,
	// 'KFUULBPMEBKPXETRFHVIJXDVAU' => 1,
	// 'Z5FXBT455NXT3UIWKJQEZEVHPQ' => 1,
	// 'BBHRVAALGMVYHRGNTPLGM7A5XA' => 1,
	// 'MWHUCVDVL2OVHQ2OVPMWASUTSY' => 1,
	// 'ZCZCU7TC5IKFWFYYLLLC2ITWTE' => 1,
	// 'WXH4FIXUCBZXGKYYH3U4NN3EVI' => 1,
	// '62C3B2ZULU4RPEF7JSNDCHEZLI' => 1,
	// 'CUZUIWNDYCFNWBIECRS2ABC3EU' => 1,
	// 'UH2336ELHSUC74SZMT7TYX6NLQ' => 1,
	// 'YHUWOBLZMW2X6ZM5MSPN7KOGQU' => 1,
	// 'RGNRPVUKQT7MADCJHXDXT6CHB4' => 1,
	// 'VOLLVXR3FBQYF7I63FNAOGPSKM' => 1,
	// 'KE3OSZUJ4U4QIHNVOWFZFUJUYM' => 1,
	// 'WQKA5H65QZKUMFPHBMXRFZNTPA' => 1,
	// 'N3Y25LHXF2YTS6UKUIW6YLBZYI' => 1,
	// 'EGDYCQBE3RSGDFK2HDRIJVNTA4' => 1,
	// 'JEGO2NEBFNUOITKC5L24RKGEXA' => 1,
	// 'OBNNZRUG42RWMJELUGDYAZUOAE' => 1,
	// 'PWMF2L3RL7MSZXHYEQIGLBURLE' => 1,
	// '3RPMLU4GPWZMEOPAMBQMLVKW6M' => 1,
	// 'IZWP743CMHXL37EPBYKUUK235Q' => 1,
	// 'CAAZ6KSA2SGQRSJJVFZZAY5RDA' => 1,
	// 'FR7Q7STFNSMFGXDRHSWHMSDNMY' => 1,
	// 'ANUELI3OI7RGPKIIICMVDYQDR4' => 1,
	// '5R73O2FKF3BGQ67HOZULZUQS6M' => 1,
	// 'S2BG27GNQWHOXB6MRLTMOTBIRM' => 1,
	// 'F4N4T73MMEAJMOB4FBIWZBXFMA' => 1,
	// 'R2ETPPOKLM4IWK2HZLSOTP3PS4' => 1,
	// 'GEH4UDGT3D5BRLA2CGS6DAEHKM' => 1,
	// '3KVU55BHFQECLFZG3455HGMHW4' => 1,
	// 'IZZ7JJQVPX5KD6232ZJW5SSVMA' => 1,
	// '7ALH4PX2JWFGGKLEDPXXZ75E4Q' => 1,
	// '35WIKAYT57J5AVP2AL3OKP6TVA' => 1,
	// 'ORQQN6ZXTFP6I54XS6DSRGYAGM' => 1,
	// 'TP3CEYKBL5MNYUV4UCG26AVJVY' => 1,
	// '2PQNEMBK4MFFW43IIZZJRARWYQ' => 1,
	// 'AD4PWGNQBBORUTQMYWZRPOL5MU' => 1,
	// '4YOA5IB4GDSQL36LONR4WDAX6M' => 1,
	// 'IWJGLTG7MDZIVAHAHVUP2XCXAI' => 1,
	// 'OTHC3BU4ZOHWMUPU3FXLA6GDLU' => 1,
	// 'WL3DJQEH7UZJ2YCDYOEDH6VLYU' => 1,
	// 'FSLRMZXMRX3HSPSI7KKSSOQAYM' => 1,
	// 'LSZ7MWMY2TIHSYRGMTNZMDJUMY' => 1,
	// 'G4PIEXITULC34Y7RYYRD3T6D7U' => 1,
	// 'K2OS7KF7YJBV774YJ6XVV35MFY' => 1,
	// 'YP5Y5IPO36YWGTEKF4V2UO6IKM' => 1,
	// 'KRS6WQA4QX3OZTUFL3IBLUK5JQ' => 1,
	// 'IDSOCWKYHDYG7XMSFNX6WK7TN4' => 1,
	// 'K434HWEQGK5WGZMVJMT6TJ6API' => 1,
	// 'BZ5G2A742KTH4UYSVEDOBUVST4' => 1,
	// 'KGVVI4WWGCQJDEGJHLM77ER2ZA' => 1,
	// 'O74M3LZARNBEC5POWUMMSBUDEU' => 1,
	// 'V5DPWTVNE5FGDICQXXKP2SUSPI' => 1,
	// '2NIUC7LNI7U4H7OKDPYH5GOEHU' => 1,
	// '3WBQKMR2JCLHXEAX3VUBJ6L3SY' => 1,
	// 'UHKJXPNPQY2Y4TCGXND4RKJMPA' => 1,
	// 'MFP5I5ZTKSFPYB7LBQLFRC4FHU' => 1,
	// '54OVWLA6YTQDAML3N7M4UKXAEA' => 1,
	// 'KZVWUAFTSZLEEBKJ42VSMRAZBM' => 1,
	// '46YVBQNLQBC6OFAH6QFV5MKNCY' => 1,
	// '3GSF4YZAD2NFWKL6IEWB62UY34' => 1,
	// 'CHZZIXGMW6IYU5F5INPTKKRCLA' => 1,
	// 'PIVWQJN2OQG4PQGNIWXNCPNXC4' => 1,
	// 'UOU6DEA7VI2QOYNHAARERC6KMA' => 1,

	// MORE FROM HAFEEZ ON FRIDAY 4/13
	// 'OHUJUKJCLNVGZ4RD3OAU33EL64' => 1,
	// 'N5INBHBY5GBYDSU2GYWMKSDNG4' => 1,
	// '656JS4UGWQKB5JNCUQYBRBWZZE' => 1,
	// 'TFXDSDWVFOEEDMBZ7WX7IAATC4' => 1,
	// '7XOLGUTCKHCEVLXA6TRTWODAOA' => 1,
	// 'KVIOPLGGWHOEUNOYZE2GS4L3UY' => 1,
	// 'RQUTCJAQQ3XATJX46JUY2NH4RI' => 1,
	// 'PZ2A5LFRKRUW2EG52GJJBLWE2E' => 1,
	// 'PXUMZISEEULJ5EQRWRZVED7K4Q' => 1,
	// '7KF5575E3CICIUT7XMCABMEZCA' => 1,
	// 'G4TJUZG7VF3QY6AQ5TPIMGOZXU' => 1,
	// 'CUZUIWNDYCFNWBIECRS2ABC3EU' => 1,
	// '5URUJQ7RRXTVSRAZHMXWWF6GNI' => 1,
	// 'ZEFZOSOMVH5QLR2OLBGEQKTBWE' => 1,
	// '33T7EKFOMQCVLYISHE4VYC3WJA' => 1,
	// 'AGUSVHMOGZJSMUEP73ZSMCAHLQ' => 1,
	// 'ZWZMQDSA4APZEBXAR3T7JH6MYU' => 1,
	// 'RNQSD22Q3UHHNUA7VYCM5HJUTY' => 1,
	// 'GG7VJJTGBFR4VKQRMUXSDTOGOY' => 1,
	// 'SQJ2CD5NUWMMEHZFFUGQ6PQQFQ' => 1,
	// 'QIMJ4ETR5YFRRG6KNX2GPNCS5I' => 1,
	// 'WXMCBQRT7D2JGPUIZQ24G3PBLM' => 1,
	// '4QWLX5GT53AM3XNH7SBJ26ZGZQ' => 1,
	// 'I4UK3MWRRZTUD2L65TXNVKIWWY' => 1,
	// 'EWHQ4KI4ZJBXL5Y2ZUPBYD5YO4' => 1,
	// 'SDX6P7LUB6T2D7GVVR6TN5DNRM' => 1,
	// 'X7MC323E6F4A55427EUCKOUB7A' => 1,
	// 'AKJ2QV45EKLRMN45Z5FMYZCV3Q' => 1,
	// 'NUR64SUDJB6N5PYV7WZAXCBSYU' => 1,
	// 'BQKSZTDN2BAE3Z6NW5IURRJOEM' => 1,	

	// me	
	'FI3SFWX5YUMNC57AOOIW2UTAC4' => 1 // Asad Sheth
);

// vibe list - two options here
// $ALL_VIBES = json_decode(file_get_contents($CACHE_DIR . 'all_vibes.json'), true); // get vibes from disk
fwrite($logp, 'vibe list definition ======' . "\n");
require('../shared/allvibes.php'); // refresh vibe metadata; do this at least periodically to update info
fwrite($logp, 'END vibe list definition ======' . "\n");

// write the vibe list to disk
file_put_contents($CACHE_DIR . "all_vibes.json", json_encode($ALL_VIBES));
file_put_contents($CACHE_DIR . "all_vibes.jsonp", 'jsonp_parse_vibes(' . json_encode($ALL_VIBES) . ');');

// content work
for($ind = 0; $ind < count($ALL_VIBES); $ind++)	{
	fwrite($logp, 'working on ' . $ALL_VIBES[$ind]['name'] . " - " . $ALL_VIBES[$ind]['id'] . "\n");

	// go get the posts
	$posts = get_vibe_posts($ALL_VIBES[$ind]);

	// shortcuts
	$vibe_id = $ALL_VIBES[$ind]['id'];
	$vibe_name = $ALL_VIBES[$ind]['name'];

	// write stuff to file
	file_put_contents($CACHE_DIR . "$vibe_id.json", json_encode($posts));
	// write stuff to file
	file_put_contents($CACHE_DIR . "$vibe_id.jsonp", 'jsonp_parse_post_list(' .json_encode($posts) . ');');
}

// let's go get comment info
$every_single_comment = array();
for($ind = 0; $ind < count($ALL_VIBES); $ind++)	{
	fwrite($logp, 'working on comment requests for ' . $ALL_VIBES[$ind]['name'] . " - " . $ALL_VIBES[$ind]['id'] . "\n");

	// shortcuts  for this vibe
	$vibe_id = $ALL_VIBES[$ind]['id'];
	$vibe_name = $ALL_VIBES[$ind]['name'];

	// all posts for this vibe we have (i think we just finished writing it here in the previous for loop)
	$posts = json_decode(file_get_contents($CACHE_DIR . "$vibe_id.json"), true);

	// messages for this whole vibe
	// TODO GET THIS FROM FILE FIRST IF IT EXISTS
	// $msgs = get_vibe_messages($posts);
	$msgs = array();

	for($i = 0; $i < count($posts); $i++)	{		
		// fwrite($logp, $vibe_name . ': working on comment requests for post #' . $i . ' - ' . $posts[$i]['title'] . "\n");

		// call the function to get comments for this context
		if($COMMENTS_PER_POST > 0) {
			$messages_for_this_post = get_context_comments($posts[$i]['content_id']);
		}
		else {
			$messages_for_this_post = array();
		}

		// go through all them message and see if we got any
		$found_message = false;
		// $post_comment_blob = '';
		for($j = 0; $j < count($messages_for_this_post); $j++)	{
			$msg = create_msg_from_canvass_response($messages_for_this_post[$j], $posts[$i]);

			if(
				// author is real? comment not deleted?
				isset($msg['author_name'])
				&& isset($msg['author_img'])
				// upvotes is not negligible?
				// && $uv > 1
				// comment text doesn't have "yahoo" in it?
				&& !strstr(strtolower($msg['text']), 'yahoo')
			)	{
				// add it to the end!
				array_push($msgs, $msg);
				$found_message = true;
				// track the blob for later
				// $post_comment_blob .= $message_text . ' ';
			}
		}

		/*
		// TODO split the blob and tag cloud logic out into a function
		// split the blob
		$post_comment_tokens = explode(' ', $post_comment_blob);
		$post_comment_token_histogram = array();
		$post_comment_bigram_histogram = array();
		// this is disabled for now
		for($j = 0; false && $j < count($post_comment_tokens); $j++)	{
			$post_comment_tokens[$j] = strtolower(trim($post_comment_tokens[$j], "\W\.\?\,\!"));
			$token = $post_comment_tokens[$j];

			if(strlen($token) == 0) continue;

			if($j > 0 && j < count($post_comment_tokens) - 1)	{
				$bigram = $post_comment_tokens[$j - 1] . ' ' . $post_comment_tokens[$j];
				$post_comment_bigram_histogram[$bigram] = isset($post_comment_bigram_histogram[$bigram]) ? ($post_comment_bigram_histogram[$bigram] + 1) : 1;
			}

			$post_comment_token_histogram[$token] = isset($post_comment_token_histogram[$token]) ? ($post_comment_token_histogram[$token] + 1) : 1;
		}
		// checking to see if it's an empty histogram
		if(strlen(json_encode($post_comment_token_histogram)) > 5000) {
			// echo json_encode($post_comment_token_histogram);
			// exit;
		}
		*/

		// now package this post to be shown - comment first, or bot first when there are no comments
		if($found_message)	{
			// good!
		}
		else {
			// oops, let's just add a "comment" to hack this post together and pretend our bot posted it
			array_push($msgs, array(
				'text' => null,
				'message_id' => '?',
				'context_id' => $posts[$i]['content_id'],
				'post_id' => $posts[$i]['post_id'],
				'upvotes' => '0',
				'downvotes' => '0',
				'reports' => '0',
				'replies' => '0',
				'author_name' => 'Newsroom Bot',
				'author_img' => 'https://s.yimg.com/ge/myc/newsroom_app_icon_ios.png',
				'author_guid' => '1',
				'score' => 0,
				'context_meta' => json_decode(json_encode($posts[$i]), true),
				'created_at' => 0,
				'comment_time' => $posts[$i]['content_relative_time'],				
				'comment_relative_time' => $posts[$i]['content_relative_time'],
				'bot' => true,
				'whitelisted_commenter' => false
			));
		}
	}

	// done with ALL the posts for this vibe

	// go through what we've got now for this whole vibe and figure out the reply situation
	for($i = 0; $i < count($msgs); $i++)	{
		if(
			// skip posts we already know had no messages
			$msgs[$i]['message_id'] == '?'
			// skip messages with few replies
			|| $msgs[$i]['replies'] < $REPLY_FETCH_COUNT_THRESHOLD
			// skip messages where the message itself is low quality
			|| $msgs[$i]['score'] < $REPLY_FETCH_QUALITY_THRESHOLD
		) {
			// oops, this one is not a user comment orrr it has no replies or it is low-scoring
			continue;
		}
		else {
			echo $i . "\n";
			fwrite($logp, $vibe_name . ': working on comment replies for message #' . $i . ' of ' . count($msgs) . "\n");
			$replies_obj = curl_canvass_replies($msgs[$i]['context_id'], $msgs[$i]['message_id']);
			$reply_list = $replies_obj['canvassReplies'];

			// parse replies out
			$reps = array();
			for($j = 0; $j < count($reply_list); $j++) {
				$reply_text = $reply_list[$j]['details']['userText'];
				$reply_id = $reply_list[$j]['messageId'];
				$reply_upvotes = $reply_list[$j]['reactionStats']['upVoteCount'];
				$reply_downvotes = $reply_list[$j]['reactionStats']['downVoteCount'];
				$reply_reports = $reply_list[$j]['reactionStats']['abuseVoteCount'];
				$reply_replies = $reply_list[$j]['reactionStats']['replyCount'];
				$reply_author_name = $reply_list[$j]['meta']['author']['nickname'];
				$reply_author_img = $reply_list[$j]['meta']['author']['image']['url'];
				$reply_context_id = $reply_list[$j]['contextId'];
				// $reply_context_meta = json_decode(json_encode($posts[$i]), true);
				$reply_created_at = $reply_list[$j]['meta']['createdAt'];
				$score = reddit_score($reply_upvotes, $reply_downvotes, $reply_reports);

				$msg = array(
					'text' => $reply_text,
					'message_id' => $reply_id,
					'context_id' => $reply_context_id,
					'upvotes' => $reply_upvotes,
					'downvotes' => $reply_downvotes,
					'reports' => $reply_reports,
					'replies' => $reply_replies,
					'author_name' => $reply_author_name,
					'author_img' => $reply_author_img,
					'score' => $score,
					// 'context_meta' => $reply_context_meta,
					'created_at' => $reply_created_at,
					'comment_relative_time' => floor((time() - $reply_created_at) / 3600),
					'bot' => false
				);

				// add this message to the list of replies
				array_push($reps, $msg);
			}

			// sort the reply list by score - NOT time-weighted score
			for($j = 0; $j < count($reps); $j++) {
				for($k = $j + 1; $k < count($reps); $k++) {
					if($reps[$j]['score'] < $reps[$k]['score'])	{
						$tmp = $reps[$j];
						$reps[$j] = $reps[$k];
						$reps[$k] = $tmp;
					}
				}
			}

			// only store one reply to the message at this point
			$msgs[$i]['ripostes'] = $reps[0];
		}
	}

	// before we do any re-sorting, write a raw deduped post list. this should match the stream sorting. start by spitting out a jsonp for this vibe; only one comment per post - the algo "top" comment
	$seen_posts = array();
	$deduped_msgs = array();
	for($i = 0; $i < count($msgs); $i++)	{
		if(!isset($seen_posts[$msgs[$i]['context_meta']['post_id']]))	{
			$seen_posts[$msgs[$i]['context_meta']['post_id']] = true;
			array_push($deduped_msgs, $msgs[$i]);
		}
		else {
			// let's move on, we're covered here already
		}
	}	
	file_put_contents($CACHE_DIR . "c_rawrank_$vibe_id.json", json_encode($deduped_msgs));	
	file_put_contents($CACHE_DIR . "c_rawrank_$vibe_id.jsonp", 'jsonp_parse_posts(' .json_encode($deduped_msgs) . ');');

	// TODO now re-rank by just tiered content time
	// for($i = 0; $i < count($msgs); $i++)	{
		// for($j = $i + 1; $j < count($msgs); $j++)  {			
			// if($msgs[$i]['score'] < $msgs[$j]['score'])	{
				// $tmp = $msgs[$i];
				// $msgs[$i] = $msgs[$j];
				// $msgs[$j] = $tmp;
			// }
		// }
	// }

	// now just rank all of the messages by message score
	for($i = 0; $i < count($msgs); $i++)	{
		for($j = $i + 1; $j < count($msgs); $j++)  {			
			if(
				$msgs[$i]['score'] < $msgs[$j]['score']
			)	{
				$tmp = $msgs[$i];
				$msgs[$i] = $msgs[$j];
				$msgs[$j] = $tmp;
			}
		}
	}

	// write the full set of ranked-by-score comments for this vibe
	file_put_contents($CACHE_DIR . "c_full_$vibe_id.json", json_encode($msgs));
	file_put_contents($CACHE_DIR . "c_full_$vibe_id.jsonp", 'jsonp_parse_comment_list(' .json_encode($msgs) . ');');

	// add all these comments to our full megalist tracker; skip our special vibes
	if(!strstr($vibe_id, '@')) {
		$every_single_comment = array_merge($every_single_comment, $msgs);
	}

	// rank the messages by a weighted score
	for($i = 0; $i < count($msgs); $i++)	{		
		for($j = $i + 1; $j < count($msgs); $j++)  {
			// weight it by comment age?
			// $weighted_timestamp_i = time_weighted_power($msgs[$i]['comment_relative_time']) + $msgs[$i]['score'];
			// $weighted_timestamp_j = time_weighted_power($msgs[$j]['comment_relative_time']) + $msgs[$j]['score'];

			// weight it by content age + score?
			// $weighted_timestamp_i = time_weighted_power($msgs[$i]['context_meta']['content_relative_time']) + $msgs[$i]['score'];
			// $weighted_timestamp_j = time_weighted_power($msgs[$j]['context_meta']['content_relative_time']) + $msgs[$j]['score'];

			// weight it by content age only?
			$weighted_timestamp_i = time_weighted_power($msgs[$i]['context_meta']['content_relative_time']);
			$weighted_timestamp_j = time_weighted_power($msgs[$j]['context_meta']['content_relative_time']);
			
			if(
				$weighted_timestamp_i < $weighted_timestamp_j
			)	{
				$tmp = $msgs[$i];
				$msgs[$i] = $msgs[$j];
				$msgs[$j] = $tmp;
			}
		}
	}

	// spit out a jsonp for this vibe; only one comment per post
	$seen_posts = array();
	$deduped_msgs = array();
	for($i = 0; $i < count($msgs); $i++)	{
		if(!isset($seen_posts[$msgs[$i]['context_meta']['post_id']]))	{
			$seen_posts[$msgs[$i]['context_meta']['post_id']] = true;
			array_push($deduped_msgs, $msgs[$i]);
		}
		else {
			// let's move on, we're covered here already
		}
	}
	
	// resort this thing now on weighted timestamp
	for($i = 0; $i < count($deduped_msgs); $i++)	{
		for($j = $i + 1; $j < count($deduped_msgs); $j++)  {
			// weight it by comment age and comment score?
			// $weighted_timestamp_i = time_weighted_power($deduped_msgs[$i]['comment_relative_time']) + $msgs[$i]['score'];
			// $weighted_timestamp_j = time_weighted_power($deduped_msgs[$j]['comment_relative_time']) + $msgs[$j]['score'];

			// weight it by content age and comment score?
			// $weighted_timestamp_i = time_weighted_power($deduped_msgs[$i]['context_meta']['content_relative_time']) + $deduped_msgs[$i]['score'];
			// $weighted_timestamp_j = time_weighted_power($deduped_msgs[$j]['context_meta']['content_relative_time']) + $deduped_msgs[$j]['score'];			

			// weight it by content age only?
			$weighted_timestamp_i = time_weighted_power($deduped_msgs[$i]['context_meta']['content_relative_time']);
			$weighted_timestamp_j = time_weighted_power($deduped_msgs[$j]['context_meta']['content_relative_time']);			

			if(
				$weighted_timestamp_i < $weighted_timestamp_j
			)	{
				$tmp = $deduped_msgs[$i];
				$deduped_msgs[$i] = $deduped_msgs[$j];
				$deduped_msgs[$j] = $tmp;
			}
		}
	}

	// write a file that represents the pseudo vibe stream, ranked and with a single comment
	file_put_contents($CACHE_DIR . "c_$vibe_id.json", json_encode($deduped_msgs));
	file_put_contents($CACHE_DIR . "c_$vibe_id.jsonp", 'jsonp_parse_posts(' . json_encode($deduped_msgs) . ');');
}

fwrite($logp, '======= starting to re-sort all comments' . "\n");
// resort all comments by score
for($i = 0; $i < count($every_single_comment); $i++)	{
	for($j = $i + 1; $j < count($every_single_comment); $j++)	{
		// time weighted score
		$weighted_timestamp_i = time_weighted_power($every_single_comment[$i]['comment_relative_time']) + $every_single_comment[$i]['score'];
		$weighted_timestamp_j = time_weighted_power($every_single_comment[$j]['comment_relative_time']) + $every_single_comment[$j]['score'];

		// non-weighted score
		$weighted_timestamp_i = $every_single_comment[$i]['score'];
		$weighted_timestamp_j = $every_single_comment[$j]['score'];
		
		if(
			$weighted_timestamp_i < $weighted_timestamp_j
		)	{
			$tmp = $every_single_comment[$i];
			$every_single_comment[$i] = $every_single_comment[$j];
			$every_single_comment[$j] = $tmp;
		}
	}	
}
fwrite($logp, '======= done re-sorting all comments' . "\n");

// write out the full list of comments
file_put_contents($CACHE_DIR . "c_allvibes.jsonp", 'jsonp_parse_all_comments(' . json_encode($every_single_comment) . ')'); 
file_put_contents($CACHE_DIR . "c_allvibes.json", json_encode($every_single_comment)); 

// deeper analysis
// fwrite($logp, '======= deeper analysis: multiposters' . "\n");
// $multi_posters = (derive_multi_posters($every_single_comment));
// write this set of users to file
// file_put_contents($CACHE_DIR . "multi_posters.json", json_encode($multi_posters));
// file_put_contents($CACHE_DIR . "multi_posters.jsonp", 'jsonp_parse_multi_posters(' .json_encode($multi_posters) . ');');
// write each user's history
// for($i = 0; $i < count($multi_posters); $i++)	{
	// $guid = $multi_posters[$i];
	// $msgs = get_user_message_history($guid);

	// write this individual user history to file
	// file_put_contents($CACHE_DIR . "user_history_$guid.json", json_encode($msgs));
	// write this individual history to a callable file
	// file_put_contents($CACHE_DIR . "user_history_$guid.jsonp", 'jsonp_parse_user_history(' .json_encode($msgs) . ');');
// }
// fwrite($logp, '======= deeper analysis: uuid_to_vibes' . "\n");
// $uuid_to_vibes = (get_uuid_to_vibes($every_single_comment));
// fwrite($logp, '======= done with deeper analysis' . "\n");


// write out a superset of all vibes for a full home stream
fwrite($logp, '======= starting amalgamation' . "\n");
$amalgam = array();
$amalgam_cards = array();
fwrite($logp, '======= getting whitelisted comments' . "\n");
$whitelisted_messages = get_whitelisted_comments();
for($ind = 0; $ind < count($ALL_VIBES); $ind++)	{
	// shortcuts
	$vibe_id = $ALL_VIBES[$ind]['id'];
	$vibe_name = $ALL_VIBES[$ind]['name'];
	$vibe_meta = $ALL_VIBES[$ind]['meta'];
	fwrite($logp, 'amalgamation for ' . $vibe_name . "\n");

	$vibe_posts = json_decode(file_get_contents($CACHE_DIR . "$vibe_id.json"), true);
	$vibe_comments = json_decode(file_get_contents($CACHE_DIR . "c_full_$vibe_id.json"), true);
	$vibe_postscomments = json_decode(file_get_contents($CACHE_DIR . "c_$vibe_id.json"), true);

	// filter postscomments for this vibe for rules, e.g. whitelists
	for($i = 0; $i < count($vibe_postscomments); $i++) {
		// this first if statement checks if any of our whitelisted commenters have posted on this story
		if(isset($whitelisted_messages[$vibe_postscomments[$i]['context_id']])) {
			// replace this in the current list with the whitelisted one
			$keys_to_copy = array(
				'author_guid',
				'author_img',
				'author_name',
				'comment_relative_time',
				'comment_time',
				'created_at',
				'downvotes',
				'replies',
				'reports',
				'text',
				'upvotes'
			);

			// copy the keys over - we're doing this because we don't have the post metadata i guess?
			for($j = 0; $j < count($keys_to_copy); $j++)	{
				$vibe_postscomments[$i][$keys_to_copy[$j]] = $whitelisted_messages[$vibe_postscomments[$i]['context_id']][$keys_to_copy[$j]];
			}

			// hack to introduce diversity in our whitelisted commenters, even though they're all asad ;)
			// check if it's asad first
			if($vibe_postscomments[$i]['author_guid'] == 'FI3SFWX5YUMNC57AOOIW2UTAC4') {
				// it is! hack a random name together
				$random_commenter_names = array(
					'aescalus',
					'brienne',
					'Snoop247',
					'OmarFromTheWire',
					'gettyImagines',
					'Thumbelina.3',
					'carbonarofx',
					'dope-o-mine'
				);

				$vibe_postscomments[$i]['author_name'] = $random_commenter_names[array_rand($random_commenter_names)];
			}
			
			// housekeeping to ensure that our proprietary fields work
			$vibe_postscomments[$i]['bot'] = false;
			$vibe_postscomments[$i]['score'] = 1;
			$vibe_postscomments[$i]['whitelisted_commenter'] = true;
		}

		// if the organically ranked comment happened to come from a whitelisted commenter... (super unlikely, right?)
		if(
			$vibe_postscomments[$i]['whitelisted_commenter']
		)	{
			// good! feature this comment on the main stream
		}
		else {
			// no! bad commenter! pretend this is a bot post
			$vibe_postscomments[$i]['bot'] = true;
		}
	}

	// add this vibe to the full array
	array_push($amalgam_cards, array(
		'id' => $vibe_id,
		'name' => $vibe_name,
		'meta' => $vibe_meta,
		'posts' => $vibe_posts,
		'comments' => $vibe_comments,
		'postscomments' => $vibe_postscomments
	));
}

// re-sort the vibe cards by most recently updated
for($i = 0; $i < count($amalgam_cards); $i++)	{
	for($j = $i + 1; $j < count($amalgam_cards); $j++)	{
		// shortcuts
		$i_vibe_id = $amalgam_cards[$i]['id'];
		$i_vibe_name = $amalgam_cards[$i]['name'];
		$i_vibe_posts = $amalgam_cards[$i]['posts'];

		if(strstr($i_vibe_id, '@')) {
			$amalgam_cards[$i]['type'] = $i_vibe_id;
		}
		else {
			$amalgam_cards[$i]['type'] = 'VIBE';
		}
		
		// reorder everything except the explicitly selected ones
		if(
			true
			&& $i_vibe_id != '@NTKVIDEO' // this makes sure the NTK is at the top
			&& $i_vibe_id != '@BREAKING' // this makes sure breaking news is at the top
		) {
			$j_vibe_posts = $amalgam_cards[$j]['posts'];

			// find the most recent pubdate in the i vibe
			$max_i_pub_at = 0;
			for($k = 0; $k < count($i_vibe_posts); $k++)	{
				$max_i_pub_at = max($max_i_pub_at, $i_vibe_posts[$k]['content_published_at']);
			}

			// find the most recent pubdate in the j vibe
			$max_j_pub_at = 0;
			for($k = 0; $k < count($j_vibe_posts); $k++)	{
				$max_j_pub_at = max($max_j_pub_at, $j_vibe_posts[$k]['content_published_at']);
			}

			// if i is less recent than j, move j ahead of i
			if($max_i_pub_at < $max_j_pub_at)	{
				$tmp = $amalgam_cards[$i];
				$amalgam_cards[$i] = $amalgam_cards[$j];
				$amalgam_cards[$j] = $tmp;
			}
		}
	}	
}

// add breaking news;
// TODO make this conditional
array_unshift($amalgam_cards, array(
	'type' => '@BREAKING',
	'id' => '@BREAKING',
	'heading' => 'Morning Brief',
	'msg' => 'Report: Trump cracks joke about Melania during her first public appearance, Miss America pageant ends the swimsuit contest and Philadelphia mayor says Trump is an \'egomaniac\'',
	'postscomments' => null
));
// add suggested vibes
// $sugg_vibes = get_suggested_vibes();
// write it out

// build our explore stuff
$amalgam_explore = array();
for($i = 0; $i < count($amalgam_cards); $i++)	{
	$i_vibe_postscomments = $amalgam_cards[$i]['postscomments'];

	for($j = 0; $j < count($i_vibe_postscomments); $j++)	{
		if($i_vibe_postscomments[$j]['whitelisted_commenter']) {
			// add this guy to our explore list
			array_push($amalgam_explore, array(
				'type' => 'comment',
				'blob' => $i_vibe_postscomments[$j]
			));
		}
	}
}

$amalgam['cards'] = $amalgam_cards;
$amalgam['explore'] = $amalgam_explore;

// write the amalgamation to disk
file_put_contents($CACHE_DIR . "amalgam.json", json_encode($amalgam));
file_put_contents($CACHE_DIR . "amalgam.jsonp", 'jsonp_parse_amalgam(' . json_encode($amalgam) . ');');

fwrite($logp, '======= done amalgamation' . "\n");

// use amalgam to write out all unique posts
fwrite($logp, '======= threadlines starting' . "\n");

$all_posts = array();
$all_posts = json_decode(file_get_contents($CACHE_DIR . "threadlines.json"), true);
fwrite($logp, '======= threadlines legacy posts count: ' . count($all_posts) . "\n");

foreach($all_posts as $content_id => $post)	{
	$all_posts[$content_id]['content_relative_time'] = floor((time() - $post['content_published_at']) / 3600);
}

for($i = 0; $i < count($amalgam_cards); $i++)	{
	if($amalgam_cards[$i]['type'] == '@BREAKING') {
		continue;
	}
	else {
		for($j = 0; $j < count($amalgam_cards[$i]['posts']); $j++)	{
			$post = $amalgam_cards[$i]['posts'][$j];
			$all_posts[$post['content_id']] = $post;			
		}
	}
}

fwrite($logp, '======= threadlines ending' . "\n");

// write the threadlines to disk
file_put_contents($CACHE_DIR . "threadlines.json", json_encode($all_posts));
file_put_contents($CACHE_DIR . "threadlines.jsonp", 'jsonp_parse_threadlines(' . json_encode($all_posts) . ');');



// update the newsroomish git
if($UPDATE_NEWSROOMISH)	{
	fwrite($logp, '======= pushing to newsroomish git' . "\n");
	exec('./newsroomish_updates.sh');
	fwrite($logp, '======= done pushing to newsroomish git' . "\n");
}

fwrite($logp, '=================== END ' . date('jS F h:i:sA e') . "\n");
fclose($logp);
?>
