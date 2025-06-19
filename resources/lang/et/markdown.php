<?php

return [

    'cheatsheet' => '
<p>Markdown on tekstist HTML-iks teisendamise süntaks veebikirjutajatele. Markdown võimaldab sul kirjutada kergesti loetavas ja kirjutatavas lihttekstivormingus, mis teisendatakse struktuurselt kehtivaks HTML-iks.</p>

<h3>Pealkirjad</h3>
<pre class="language-markdown"><code># See on H1
## See on H2
### See on H3 jne.
</code></pre>

<h3>Rasvane & Kursiiv</h3>
<pre class="language-markdown"><code>Saad muuta teksti *kursiivi*, **rasvaseks** või _**mõlemat korraga**_.</code></pre>

<h3>Lingid</h3>
<pre class="language-markdown"><code>See on [näidislink](http://example.com).</code></pre>

<h3>Kood</h3>
<p>
Ümbritse oma kood kolme tagurpidi ülakomaga (<code>```</code>) nii enne kui ka pärast koodiplokki.
</p>

<pre class="language-markdown"><code>```
see: on natuke yaml-i
```</code></pre>

<p>Saad lisada koodi ka reasiseselt, ümbritsedes sisu ühe tagurpidi ülakomaga <code>`</code>.</p>

<h3>Tsitaat</h3>

<p>Loo plokktsitaat, alustades oma teksti sümboliga <code>> </code>.</p>

<pre class="language-markdown"><code>> See on plokktsitaat.</code></pre>

<h3>Pildid</h3>
<pre class="language-markdown"><code>![alternatiivtekst](http://example.com/image.jpg)</code></pre>

<h3>Järjestamata nimekiri</h3>
<pre class="language-markdown"><code>- Peekon
- Steik
- Õlu</code></pre>

<h3>Järjestatud nimekiri</h3>
<pre class="language-markdown"><code>1. Söö
2. Joo
3. Ole rõõmus</code></pre>

<h3>Tabelid</h3>

<pre class="language-markdown"><code>Esimene päis  | Teine päis
------------- | -------------
Lahtri sisu   | Lahtri sisu
Lahtri sisu   | Lahtri sisu</code></pre>',

];