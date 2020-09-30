<?php

return [
    'cheatsheet' => '
    <p>Markdown er et tekst-til-HTML format. Markdown giver dig mulighed for at skrive ved hjælp af et let læseligt, let at skrive almindeligt tekstformat, der konverteres til HTML.</p>
    
    <h3>Overskrifter</h3>
    <pre class="language-markdown"><code># Dette er en h1
## Dette er en h2
### Dette er en h3</code></pre>
    
    <h3>Fremhævet &amp; fed</h3>
    <pre class="language-markdown"><code>Du kan lave tekst *fremhævet*, **fed**, eller ***begge***.</code></pre>
    
    <h3>Links</h3>
    <pre class="language-markdown"><code>Dette er et [eksempel link](http://example.com).</code></pre>
    
    <h3>Kode</h3>
    <p>
    Omgiv din kode med 3 accent grave (<code>```</code>) før og efter koden.
    </p>
    
    <pre class="language-markdown"><code>```
    dette: er noget yaml
```</code></pre>
    
    <p>Du kan også tilføje indlejret kode ved at bruge en enkelt accent grave ( <code>`</code> ).
    
    <h3>Citater</h3>
    
    <p>Opret et citat afsnit ved at starte din tekst med <code>></code>.</p>
    
    <pre class="language-markdown"><code>> Dette vil blive et citat afsnit.</code></pre>
    
    <h3>Billeder</h3>
    <pre class="language-markdown"><code>![alternativ tekst](http://example.com/image.jpg)</code></pre>
    
    <h3>Punktopstilling</h3>
    <pre class="language-markdown"><code>- Bacon
- Bøf
- Øl</code></pre>
    
    <h3>Numerisk punktopstilling</h3>
    Den fortløbende nummering bliver automatisk genereret. 
    <pre class="language-markdown"><code>1. Spis
1. Drik
1. Vær glad</code></pre>
    
    <h3>Tabeller</h3>
    
    <pre class="language-markdown"><code>Første kolonne titel  | Anden kolonne titel
--------------------- | -------------------
Celle indhold         | Celle indhold
Celle indhold         | Celle indhold</code></pre>',
];
