<?php

namespace Statamic\Console\Commands;

use Illuminate\Console\Command;
use Statamic\Console\RunsInPlease;

/**
 *  ,,,'',''''',,,;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;:::::::::::::::::::::::::::::::::::::::::::::::::::::::
 * ,',,',:looolllooool:;;;;coodol:;;;;;;;;;;;;;;;;;;::cloddolc::::lddddddddddddddol::::::::::::::::::::
 * ''''';x000000000000kl;,:x000KOl;;;;;;;;;;;;;;;;;;ldk000000xc;;;o0KKK000000K00K0Odc::::::::::::::::::
 * '''..;x0000000000000x:,:x000KOl;,;;;;;;;;;;;;;;;lO0000KK000xc;;:okOO00K0000K0OOOko::::::::::::::::::
 * '''..:k00000xc:;::::;,';x0000Ol;,,,,,,;;;;,;;;;oO0000kxO0000kc;;;::clx00000kocccc:::::::::::::::::::
 * .....;x00000klccccc:;'.,d0000Ol,,,,,,,,,,,,,,;oO0KK0x:;lk0000xc;;;;;;o00000d:;;;;;;;;;;;;;;;;;;;;;::
 * .....,x0000000000000x;.,d0000Oc''''',,,,,,,,;lO00K0kc,,;o00000d;;;;,;oO0000d:;;;;;;;;;;;;;;;;;;;;;;;
 * .....;x000000OOOOOOxc'.'d000Kk:....'''''''''ck000KK0xoodk00000Ol;,,,;oO0000x:;;;;;;;;;;;;;;;;;;;;;;;
 * .....;x0000Oo;;;;;,'...'d00000o,'',,,,,,'.';x0K00KKK0KKKK000000k:',,,o00000d:;;;;;;;;;;;;;;;;;;;;;;;
 * .....;x0000k;..........'d0K0000Okkkkkkkkc',d00000OkkxxxxkO000000o,'',l00000d:,;,,;;;;;;;;;;;;;;;;;;;
 * .....;x000Kk;..........,d0KK0000KKKK000x:'cOKKKOd;,,'''',;oOK00Kk:'''l0K0K0d;,,,,,,,,;;;;;;;;;;;;;;;
 * .....':oooo:'...........;odddddddddddoc,..;oddoc,.........'cddddl;'.';ldddo:'''''',,,,,,,,,,,,;;;;;;
 * ............................................................''''......'''''.'''.''''''''''''',,,,;;;
 * ...........;:loooo:'............,:cc:;'..............,:clc;'....,:cccc;'.....';llllllllllcc:,''',,,,
 * ........,lxO00K0000d,.........,ok0000Od;............;xO000kc'.':x00000x:.....;x0K0000000000Okdc,''''
 * .......ck000000Okkkx:........;x000000K0x:..........;x000000k:':k000K000o'....;x000000OkOO0K0000o,'''
 * ......lO0000ko:,'...........;x000kxk0KK0k;.......',o00000000kok00000000x;....,x00000d;,,:d000KKx;'''
 * .....:O0000d,..............;x0K0x;.,d0KK0x;......:dkKK000000000K00000K0Oc....,d000KOc'';lx0K00Ol'.''
 * .....l000Kk;..............,x0KKO:...:OKKK0o'....'o000000x:lO0000klx000K0d,...,d00000kdxO0KK00xc'....
 * .....l0000x,.............'d0K000dlcld0K000Oc....;kKK00KOc..lO00x;.:OK000Oc...'d0K00K000K00kd:'......
 * .....:O000Oo,.......'....o0K000K0KK00000000k;...cOKK00Kx,...lkd;..,d00000d,..,d0000000kdl:,.........
 * ......o00000Odlllodxkc..cOKK00Okxxxxxk0000K0o'..o0KK0K0o.....'.....:OK00KOc..,d000K0x:'.............
 * ......'lk00000000000k:.,xKKK0d;......'ck0K0Kk;.'d00KKKO:...........'o0KK0Kx,.,d000KO:...;c::cc:,....
 * ........'codxkkOOkxo;...cddoc..........;oddol,..:dddddc'............'codddl,..:oddoc'...;c:;ccc,....
 * ..............'''...................................................................................
 * ............  ......................................................................................
 * .. ......    .. ... .....  .........................................................................
 *
 * Committed at Flat Camp 2023
 */
class FlatCamp extends Command
{
    use RunsInPlease;

    protected $signature = 'flat:camp';
    protected $description = 'Flat Camp';

    protected $quotes = [
        "No, you're right. Let's do it the dumbest way possible. Because it's easier for you. - Erin",
        'Butter is butter. - Convenience store lady',
        'Christopher Columbus was from Poland. - Krzemo',
        'Is this a safe space? - Jack',
        'Where does Polish come from? - Erin',
        "The customs officer didn't appreciate the Saudi guy saying he was going to a training camp in the mountains. - Saud",
        'I just want to bake shop & wrap. - Mug',
        'The drone must have tried flying to its last home point. Florida. - Jack',
        'And then you sniff like a rabbit, like this. - Conrad',
        'Why? - Erin',
        'I came to Flat Camp, and I picked up smoking. - Colin',
        "I was gonna make some money but let's build a fort. - Colin",
        'To be fair (to be faiiiir) - Sylvester',
        'I love it here, they call me Indiana Jones - Rob',
        'Just a little pasta please. Or that much, sure. - Everyone',
    ];

    public function handle()
    {
        return $this->comment(collect($this->quotes)
            ->map(fn ($quote) => $this->formatForConsole($quote))
            ->random());
    }

    protected function formatForConsole($quote)
    {
        [$text, $author] = str($quote)->explode('-');

        return sprintf(
            "\n  <options=bold>“ %s ”</>\n  <fg=gray>— %s</>\n",
            trim($text),
            trim($author),
        );
    }
}
