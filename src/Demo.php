<?php

namespace Andach\DoomWadAnalysis;

define('VERSION', '0.15.0');
define('DEMOEND', 0x80);

class Demo
{
    public string $engine = '';
    public int $version = 0;
    public string $filePath = '';
    public string $map = '';
    public int $skill = 0;
    public int $episode = 0;
    public int $level = 0;
    public int $playerCount = 0;
    public int $ticCount = 0;

    protected string $data = '';
    public array $stats;
    public array $tics = [];
    public string $ticsCSV = '';

    public function __construct(string $filePath)
    {
        $this->filePath = $filePath;
    }

    function lmpStats($game = null, $debug = 0, $classic = false, $zdoom9 = false)
    {
        if (!$fp = @fopen($this->filePath, 'rb')) {
            echo "$this->filePath: cannot open\n";
            return false;
        }
        $dsdatc = FALSE;
        $ticlen = 4;
        $ticrat = 35;
        $rver = $sver = $umap = 0;
        $seed = '';
        $cls1 = $cls2 = $cls3 = $cls4 = -1;

        dsda_doom:
        $vers = $this->readByte($fp);
        // vanilla Doom <= v1.2, Heretic, Hexen
        if ($vers >= 0 && $vers <= 9) {
            $comp = $insr = 0;
            $skll = $vers + 1;
            // 0x01-0x02
            $epis = $this->readByte($fp);
            $miss = $this->readByte($fp);
            // 0x03-0x06: players 1-4 present, Hexen classes
            $ply1 = $this->readByte($fp);
            if ($game == 'X0' || $game == 'X1')
                $cls1 = $this->readByte($fp);
            $ply2 = $this->readByte($fp);
            if ($game == 'X0' || $game == 'X1')
                $cls2 = $this->readByte($fp);
            $ply3 = $this->readByte($fp);
            if ($game == 'X0' || $game == 'X1')
                $cls3 = $this->readByte($fp);
            $ply4 = $this->readByte($fp);
            if ($game == 'X0' || $game == 'X1')
                $cls4 = $this->readByte($fp);
            if ($game == 'X1') // Hexen v1.1 player/class 5-8
                $skip = fread($fp, 8);
            $mode = $resp = $fast = $nomo = $view = 0;
            if ($game == 'H' || $game == 'X0' || $game == 'X1')
                $ticlen = 6;

            // Doom alpha v0.5
        } elseif ($game == 'A' && $vers == 101) { // 'e'
            $epis = intval(fread($fp, 1));
            $skip = $this->readByte($fp);
            if ($skip != 'm')	{
                echo "version $vers unexpected signature: $skip\n";
                return false;
            }
            $miss = intval(fread($fp, 1));
            $vers = -1;
            $skll = $mode = $resp = $fast = $nomo = $view = 0;
            $comp = $insr = 0;
            $ply1 = 1;
            $ply2 = $ply3 = $ply4 = 0;
            // 0x04: tics data

            // vanilla Doom v1.4-1.9 (= offsets), TASDoom v1.10, PrBoom v1.11 longtics;
            // Doom Classic v1.11, v1.12 debug; Strife 1.01; Doom64 EX v1.4; RUDE 3.1.0pre5+
        } elseif ($vers >= 104 && $vers <= 112 || $vers == 101 || $vers == 116 || $vers == 222) {
            prDoom_um:
            if ($vers == 111 || $vers == 222)
                $ticlen = 5;
            if ($vers == 101 || $vers == 112)
                $ticlen = 6;
            $comp = $insr = 0;
            // 0x01-0x03
            $skll = $this->readByte($fp) + 1;
            if ($vers != 101 && $vers != 116)
                $epis = $this->readByte($fp);
            else
                $epis = 0;
            if (($classic && $vers == 111) || $vers == 112)
                $pack = $this->readByte($fp);
            $miss = $this->readByte($fp);
            // 0x04: play mode: 0 = Single/coop, 1 = DM, 2 = AltDeath
            $mode = $this->readByte($fp);
            // 0x05-0x07
            $resp = $this->readByte($fp);
            if ($vers == 116)
                $skip = $this->readByte($fp); // respawnitem
            $fast = $this->readByte($fp);
            $nomo = $this->readByte($fp);
            // 0x08: which player's point of view to use, zero-indexed (0 means player 1)
            $view = $this->readByte($fp);
            // RUDE extended format
            if ($vers == 222)
                $skip = fread($fp, 9);
            // 0x09-0x0C: players 1-4 present
            $ply1 = $this->readByte($fp);
            $ply2 = $this->readByte($fp);
            $ply3 = $this->readByte($fp);
            $ply4 = $this->readByte($fp);
            if ($classic || $vers == 112) {
                // health, armorpoints, armortype, readyweapon, NUMWEAPONS, NUMAMMO + maxammo
                $statelen = 4 + 9 + 4*2;
                $statelen *= 4 * ($ply1 + $ply2 + $ply3 + $ply4);
                $skip = fread($fp, $statelen);
            }
            // 0x0D: tics data

            // Doom64 EX v2.5+
        } elseif (chr($vers) == 'D') {
            if (fread($fp, 4) != "M64\0") {
                echo "invalid Doom64 EX version\n";
                return false;
            }
            $ticlen = 8;
            $ticrat = 30;
            $epis = 0;
            $comp = $insr = 0;
            // 0x05-0x06
            $skll = $this->readByte($fp) + 1;
            $miss = $this->readByte($fp);
            // 0x07: play mode: 0 = Single/coop, 1 = DM, 2 = AltDeath
            $mode = $this->readByte($fp);
            // 0x08-0x0B
            $resp = $this->readByte($fp);
            $skip = $this->readByte($fp); // respawnitem
            $fast = $this->readByte($fp);
            $nomo = $this->readByte($fp);
            // 0x0C: which player's point of view to use, zero-indexed (0 means player 1)
            $view = $this->readByte($fp);
            // 0x0D-0x10:	random seed
            $seed = unpack('N', fread($fp, 4));
            $seed = sprintf('%08X', $seed[1]);
            // 0x11-0x18:	game & compat flags
            $skip = fread($fp, 8);
            // 0x19-0x1C: players 1-4 present
            $ply1 = $this->readByte($fp);
            $ply2 = $this->readByte($fp);
            $ply3 = $this->readByte($fp);
            $ply4 = $this->readByte($fp);
            // 0x1D: tics data

            // Boom/MBF v2.00-2.04 / 2.10-2.14, CDoom v2.05-2.07
        } elseif (($vers >= 200 && $vers <= 204) ||
            ($vers >= 205 && $vers <= 207) ||
            ($vers >= 210 && $vers <= 214) || $vers == 221) {
            prBoom_um:
            $sign = fread($fp, 6);
            if ($sign != 'CDOOMC' && (ord($sign[0]) != 0x1D ||
                    (ord($sign[4]) != 0xE6 && ord($sign[5]) != 0xE6))) {
                echo "version $vers unexpected signature: $sign\n";
                return false;
            }
            if (($vers >= 205 && $vers <= 207) || $vers == 214 | $vers == 221)
                $ticlen = 5;
            $comp = $insr = 0;
            // 0x07: compatibility (0 or 1)
            if ($vers != 221)
                $comp = $this->readByte($fp);
            // 0x08-0x0A
            $skll = $this->readByte($fp) + 1;
            $epis = $this->readByte($fp);
            $miss = $this->readByte($fp);
            // 0x0B: play mode: 0 = Single/coop, 1 = DM, 2 = AltDeath
            $mode = $this->readByte($fp);
            // 0x0C: console player: 0 = 1st, 1 = 2nd, etc.
            $view = $this->readByte($fp);
            // 0x0D-0x12: expansion
            if ($vers == 221)
                $skip = fread($fp, 3);
            else
                $skip = fread($fp, 6);
            // 0x13-0x15
            $resp = $this->readByte($fp);
            $fast = $this->readByte($fp);
            $nomo = $this->readByte($fp);
            // 0x16: demo insurance (0 or 1)
            if ($vers != 221)
                $insr = $this->readByte($fp);
            // 0x17-0x1A:	random seed
            $seed = unpack('N', fread($fp, 4));
            $seed = sprintf('%08X', $seed[1]);
            // 0x1B-0x4C: expansion
            if ($vers == 221)
                $skip = fread($fp, 36);
            else
                $skip = fread($fp, 50);
            // 0x4D-0x50: players 1-4 present
            $ply1 = $this->readByte($fp);
            $ply2 = $this->readByte($fp);
            $ply3 = $this->readByte($fp);
            $ply4 = $this->readByte($fp);
            // 0x51-0x6C: future expansion
            $skip = fread($fp, 28);
            // 0x6D: tics data

            // ZDoom v1.11-1.12; ZDaemon v1.09+
        } elseif (chr($vers) == 'Z') {
            $head = fread($fp, 3);
            // ZDoom
            if ($head == 'DEM') {
                $rver = $this->readByte($fp) * 100;
                $rver += $this->readByte($fp);
                return array('vers' => ord('Y'), 'rver' => $rver);
                // ZDaemon
            } elseif ($head == "DD\0") {
                fseek($fp, 46);
                fscanf($fp, "%[0-9(). -]", $rver);
                return array('vers' => ord('X'), 'rver' => $rver);
            } else {
                echo "invalid ZDoom version\n";
                return false;
            }

            // ZDoom v1.14+
        } elseif (chr($vers) == 'F') {
            require_once __DIR__.'/lmpstats.zdoom.php';
            fseek($fp, 0);
            return lmpZDoom($fp, $debug, $zdoom9);

            // Legacy v1.29-1.44+
        } elseif ($vers >= 129 && $vers <= 144) {
            require_once __DIR__.'/lmpstats.legacy.php';
            fseek($fp, 0);
            return lmpLegacy($fp, $debug);

            // various v2.55
        } elseif ($vers == 255) {
            $sign = fread($fp, 1);

            // PrBoom+um v2.55: v2.5.1.7
            if (ord($sign[0]) >= 104 && ord($sign[0]) <= 112) {
                $vers = ord($sign[0]);
                $umap = 1;
                goto prDoom_um;
            }
            if ((ord($sign[0]) >= 200 && ord($sign[0]) <= 204) ||
                (ord($sign[0]) >= 210 && ord($sign[0]) <= 214)) {
                $vers = ord($sign[0]);
                $umap = 1;
                goto prBoom_um;
            }

            $sign .= fread($fp, 5);
            // Eternity v2.55: v3.29-4.0+
            if (strncmp($sign, "ETERN", 5) == 0) {
                // 0x07-0x0A: real version
                $rver = unpack('V', fread($fp, 4));
                $rver = $rver[1];
                // 0x0B: sub-version
                $sver = $this->readByte($fp);
                // 0x0C: compatibility (0 or 1)
                $comp = $this->readByte($fp);
                // 0x0D-0x0F
                $skll = $this->readByte($fp) + 1;
                $epis = $this->readByte($fp) + 1;
                $miss = $this->readByte($fp);
                // 0x10: play mode: 0 = Single/coop, 1 = DM, 2 = AltDeath
                $mode = $this->readByte($fp);
                // 0x11: console player: 0 = 1st, 1 = 2nd, etc.
                $view = $this->readByte($fp);
                // 0x12-0x15:	DM flags
                if ($rver >= 335)
                    $skip = fread($fp, 4);
                // 0x16-0x1D:	map name
                if ($rver >= 329)
                    $skip = fread($fp, 8);
                // 0x1E-0x23: expansion
                $skip = fread($fp, 6);
                // 0x24-0x26
                $resp = $this->readByte($fp);
                $fast = $this->readByte($fp);
                $nomo = $this->readByte($fp);
                // 0x27: demo insurance (0 or 1)
                $insr = $this->readByte($fp);
                // 0x28-0x2B:	random seed
                $seed = unpack('N', fread($fp, 4));
                $seed = sprintf('%08X', $seed[1]);
                // 0x2C-0x5D: expansion
                $skip = fread($fp, 50);
                // 0x5E-0x61: players 1-4 present
                $ply1 = $this->readByte($fp);
                $ply2 = $this->readByte($fp);
                $ply3 = $this->readByte($fp);
                $ply4 = $this->readByte($fp);
                // version dependent tic length
                if ($rver >= 335)
                    $ticlen += 1; // actions
                if ($rver >= 333)
                    $ticlen += 3; // longtic + look
                elseif ($rver >= 329)
                    $ticlen += 1; // old look
                if ($rver >= 340)
                    $ticlen += 1; // fly
                if ($rver >= 401)
                    $ticlen += 5; // item/weapon/slot

                // PrBoom+um v2.55: v2.6+
            } elseif (strncmp($sign, "PR+UM", 5) == 0) {
                // 0x07: extension version
                $ever = $this->readByte($fp);
                if ($ever != 1) {
                    echo "$sign unexpected extension format: $ever\n";
                    return false;
                }
                // 0x08-0x09: number of extensions
                $next = $this->readByte($fp);
                $next |= $this->readByte($fp) << 8;
                for ($i = 0; $i < $next; $i++) {
                    // extension length
                    $elen = $this->readByte($fp);
                    // extension name
                    $extn = fread($fp, $elen);
                    if (strncmp($extn, "UMAPINFO", $elen) != 0) {
                        echo "$sign unexpected extension: $extn\n";
                        return false;
                    }
                    $extn = fread($fp, 8);
                    if (substr($extn, 0, 3) == 'MAP') {
                        $episu = 1;
                        $missu = intval(substr($extn, 3));
                    } elseif ($extn[0] == 'E' && ($mpos = strpos($extn, 'M')) !== false) {
                        $episu = intval(substr($extn, 1, $mpos-1));
                        $missu = intval(substr($extn, $mpos+1));
                    } else {
                        echo "$sign unexpected UMAPINFO extension value $extn\n";
                        return false;
                    }
                }

                $rver = $this->readByte($fp);
                if ($rver >= 104 && $rver <= 112)
                    goto prDoom_um;
                if (($rver >= 200 && $rver <= 204) ||
                    ($rver >= 210 && $rver <= 214))
                    goto prBoom_um;

                echo "version $vers unexpected real version: $rver\n";
                return false;

                // DSDA-Doom
            } elseif (ord($sign[0]) == 0x1d && strncmp(substr($sign, 1), "DSDA", 4) == 0 && ord($sign[5]) == 0xe6) {
                $rver = $this->readByte($fp);
                // 0x08-0x0B: end marker
                $skip = fread($fp, 4);
                // 0x0C-0x0F: tics
                $tics = unpack('N', fread($fp, 4));
                $tics = $tics[1];
                if ($rver >= 2)
                    // 0x10: flags
                    $skip = $this->readByte($fp);
                if ($rver >= 3)
                    // 0x11: UDMF version
                    $skip = $this->readByte($fp);
                $dsdatc = TRUE;
                goto dsda_doom;

                // Doom + Doom II (Legacy of Rust)
            } elseif (strncmp($sign, "OSRS2", 5) == 0) {
                $ticlen = 5;
                $comp = $insr = 0;
                // 0x07-0x0A: extension version
                $ever = unpack('N', fread($fp, 4));
                // 0x0B-0x13: git version
                $skip = fread($fp, 9);
                // 0x14-0x16
                $skll = $this->readByte($fp) + 1;
                $epis = $this->readByte($fp);
                $miss = $this->readByte($fp);
                // 0x17: play mode: 0 = Single/coop, 1 = DM, 2 = AltDeath, 3 = modern DM3
                $mode = $this->readByte($fp);
                // 0x18-0x1A
                $resp = $this->readByte($fp);
                $fast = $this->readByte($fp);
                $nomo = $this->readByte($fp);
                // 0x1B: console player: 0 = 1st, 1 = 2nd, etc.
                $view = $this->readByte($fp);
                // 0x1C-0x1F: players 1-4 present
                $ply1 = $this->readByte($fp);
                $ply2 = $this->readByte($fp);
                $ply3 = $this->readByte($fp);
                $ply4 = $this->readByte($fp);

            } else {
                echo "version $vers unexpected signature: $sign\n";
                return false;
            }

        } else {
            echo "version $vers\n";
            return false;
        }
        $plys = $ply1 + $ply2 + $ply3 + $ply4;
        if ($plys == 0)
            $plys = 1;

        // tics data
        $this->debugLog(ftell($fp), $debug, 1, 'START');
        $foot = '';
        if (!$dsdatc) {
            $tics = 0;
            while ($tic = fread($fp, 4)) {
                // Check for DEMOEND marker (usually 0x80)
                if ($vers >= 0 && ord($tic[0]) == DEMOEND) {
                    $this->debugLog(ftell($fp) - strlen($tic), $debug, 1, 'DEMOEND');
                    $foot = substr($tic, 1);
                    break;
                }

                // Decode tic data
                $forward = unpack('c', $tic[0])[1]; // signed byte
                $strafe  = unpack('c', $tic[1])[1];
                $turn    = unpack('c', $tic[2])[1];
                $action  = ord($tic[3]);

                // Action flags
                $fire     = ($action & 0b00000001) !== 0;
                $use      = ($action & 0b00000010) !== 0;

                // Weapon change bits (bits 2–4)
                $weaponBits = ($action & 0b00011100) >> 2;
                $weapon = null;
                if ($action & 0b00000100) { // only interpret if a weapon change is encoded
                    $weaponMap = [
                        0 => 'fist/chainsaw',
                        1 => 'pistol',
                        2 => 'shotgun',
                        3 => 'chaingun',
                        4 => 'rocket launcher',
                        5 => 'plasma rifle',
                        6 => 'BFG9000',
                        7 => 'super shotgun',
                    ];
                    $weapon = $weaponMap[$weaponBits] ?? null;
                }

                // Bit 7 and 6 override mode
                $bit6_7 = ($action & 0b11000000) >> 6;
                $special = null;
                if ($bit6_7 === 1) {
                    $special = 'pause';
                } elseif ($bit6_7 === 2) {
                    $saveSlot = ($action & 0b00011100) >> 2;
                    $special = "save to slot $saveSlot";
                }

                $this->tics[] = [
                    'forward' => $forward,
                    'strafe'  => $strafe,
                    'turn'    => $turn,
                    'fire'    => $fire,
                    'use'     => $use,
                    'weapon'  => $weapon,
                    'special' => $special,
                ];

                $tics++;
                $this->debugLog(ftell($fp) - strlen($tic), $debug, 2, 'TIC: ' . $tics);
            }
            $foot .= fread($fp, 4*1024);
        }

        // compute tics & time
        $tics /= $plys;
        $tsec = round($tics / $ticrat, 2);
        $mins = intval($tsec / 60);
        $secs = round($tsec - $mins * 60, 2);

        $this->stats = [
            'version' => $vers,
            'skill_number' => $skll,
            'mode_number' => $mode,
            'respawn' => $resp,
            'fast' => $fast,
            'nomonsters' => $nomo,
            'number_of_players' => $plys,
            'tics' => $tics,
            'seconds' => $tsec,
        ];

        return array(
            'vers' => $vers,
            'rver' => $rver,
            'sver' => $sver,
            'umap' => $umap,
            'skll' => $skll,
            'epis' => $epis,
            'miss' => $miss,
            'mode' => $mode,
            'resp' => $resp,
            'fast' => $fast,
            'nomo' => $nomo,
            'comp' => $comp,
            'insr' => $insr,
            'seed' => $seed,
            'view' => $view,
            'ply1' => $ply1,
            'ply2' => $ply2,
            'ply3' => $ply3,
            'ply4' => $ply4,
            'plys' => $plys,
            'cls1' => $cls1,
            'cls2' => $cls2,
            'cls3' => $cls3,
            'cls4' => $cls4,
            'long' => $ticlen,
            'tics' => $tics,
            'tsec' => $tsec,
            'mins' => $mins,
            'secs' => $secs,
            'foot' => $foot,
        );
    }

    private function readByte($fp)
    {
        return ord(fread($fp, 1));
    }

    function debugLog($i, $debug, $lev, $cmd, $par = false)
    {
        if ($debug >= $lev) {
            printf("%6d %05X - %s", $i, $i, $cmd);
            if (!$par)
                echo "\n";
        }
    }

    public function convertTicsToCsv()
    {
        if (empty($this->tics) || !is_array($this->tics)) {
            $this->ticsCSV = '';
            return;
        }

        // Define CSV headers
        $headers = ['index', 'forward', 'strafe', 'turn', 'fire', 'use', 'weapon', 'special'];
        $csv = implode(',', $headers) . "\n";

        // Append each tic line
        foreach ($this->tics as $index => $tic) {
            $row = [
                $index,
                $tic['forward'] ?? '',
                $tic['strafe'] ?? '',
                $tic['turn'] ?? '',
                $tic['fire'] ?? '',
                $tic['use'] ?? '',
                $tic['weapon'] ?? '',
                $tic['special'] ?? ''
            ];
            $csv .= implode(',', $row) . "\n";
        }

        $this->ticsCSV = $csv;
    }

}
