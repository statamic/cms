<?php

return [

    'cheatsheet' => '
<p>A Markdown egy szöveg-HTML jelölési szintaxis webfejlesztők számára. A Markdown lehetővé teszi, hogy könnyen olvasható és írható egyszerű szöveges formátum hozzunk létre, amit szerkezetileg érvényes HTML-é alakítanak át.</p>

<h3>Fejlécek</h3>
<pre class="language-markdown"><code># Ez egy h1
## Ez egy h2
### Ez pedig h3 tag, és így tovább.
</code></pre>

<h3>Félkövér és Dőlt</h3>
<pre class="language-markdown"><code>Szöveges részt *kitudsz emelni*, **félkövérré tudsz alakítani**, vagy _**mindkettő**_.</code></pre>

<h3>Hivatkozások</h3>
<pre class="language-markdown"><code>Ez egy [példa hivatkozás](http://example.com).</code></pre>

<h3>Kód</h3>
<p>
Formázd a kódodat 3 aposztróffal (<code>```</code>) a kód előtt és után.
</p>

<pre class="language-markdown"><code>```
ez: egy yaml részlet
```</code></pre>

<p>A szövegrészbe is tudsz kódrészletet megadni, ha 1-1 darab aposztróf <code>`</code> közé rakod a szöveget.

<h3>Idézet</h3>

<p>Hozzon létre idézetet úgy, hogy a szöveget ezzel kezdi <code>> </code>.</p>

<pre class="language-markdown"><code>> Ez blokk idézet lesz.</code></pre>

<h3>Képek</h3>
<pre class="language-markdown"><code>![alternatív szöveg](http://example.com/image.jpg)</code></pre>

<h3>Rendezetlen lista</h3>
<pre class="language-markdown"><code>- Szalonna
- Hús
- Sör</code></pre>

<h3>Rendezett lista</h3>
<pre class="language-markdown"><code>1. Enni
2. Inni
3. Házasodni</code></pre>

<h3>Táblázat</h3>

<pre class="language-markdown"><code>Első fejléc  | Második fejléc
--------------- | -------------
Tartalom cella  | Tartalom cella
Tartalom cella  | Tartalom cella</code></pre>',

];
