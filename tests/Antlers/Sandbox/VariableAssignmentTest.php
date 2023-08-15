<?php

namespace Tests\Antlers\Sandbox;

use Facades\Tests\Factories\EntryFactory;
use Statamic\Facades\Collection;
use Statamic\Facades\Taxonomy;
use Statamic\View\Antlers\Language\Utilities\StringUtilities;
use Tests\Antlers\ParserTestCase;
use Tests\FakesViews;
use Tests\PreventSavingStacheItemsToDisk;

class VariableAssignmentTest extends ParserTestCase
{
    use FakesViews;
    use PreventSavingStacheItemsToDisk;

    public function test_simple_variable_is_set()
    {
        $result = $this->evaluate('my_variable = 1; another_var = 3;', []);

        $this->assertArrayHasKey('my_variable', $result);
        $this->assertArrayHasKey('another_var', $result);

        $this->assertEquals(1, $result['my_variable']);
        $this->assertEquals(3, $result['another_var']);

        $resultTwo = $this->evaluate('my_variable += 30;', $result);

        $this->assertEquals(31, $resultTwo['my_variable']);
        $this->assertEquals(3, $resultTwo['another_var']);
    }

    protected function assertHasVariableWithValue($variable, $value, $text)
    {
        $result = $this->evaluate($text);
        $this->assertArrayHasKey($variable, $result);

        $this->assertEquals($value, $result[$variable]);
    }

    public function test_addition_assignment()
    {
        $this->assertHasVariableWithValue(
            'test', 30, 'test = 25; test += 5;'
        );
    }

    public function test_division_assignment()
    {
        $this->assertHasVariableWithValue(
            'test', 5, 'test = 25; test /= 5;'
        );
    }

    public function test_modulus_assignment()
    {
        $this->assertHasVariableWithValue(
            'test', 0, 'test = 25; test %= 5;'
        );
    }

    public function test_multiplication_assignment()
    {
        $this->assertHasVariableWithValue(
            'test', 25, 'test = 5; test *= 5;'
        );
    }

    public function test_subtraction_assignment()
    {
        $this->assertHasVariableWithValue(
            'test', 20, 'test = 25; test -= 5;'
        );
    }

    public function test_simple_assignment_within_tag_contexts()
    {
        $template = <<<'EOT'
{{ myvar = 0 }}BEFORE{{ myvar }}|{{ loop from="1" to="10" }}{{ myvar += 1; myvar }}|{{ /loop }}AFTER{{ myvar }}
EOT;
        $expected = 'BEFORE0|1|2|3|4|5|6|7|8|9|10|AFTER10';

        $this->assertSame($expected, $this->renderString($template, [], true));
    }

    public function test_nested_tags_process_shared_assignment_data()
    {
        $template = <<<'EOT'
{{ myvar = 0 }}BEFORE{{ myvar }}|{{ loop from="1" to="10" }}{{ myvar += 1; }}{{ loop from="1" to="10" }}{{ myvar += 1; myvar }}{{ /loop }}|{{ /loop }}AFTER{{ myvar }}
EOT;

        $expected = 'BEFORE0|234567891011|13141516171819202122|24252627282930313233|35363738394041424344|46474849505152535455|57585960616263646566|68697071727374757677|79808182838485868788|90919293949596979899|101102103104105106107108109110|AFTER110';

        $this->assertSame($expected, $this->renderString($template, [], true));
    }

    public function test_assignments_are_processed_while_iterating()
    {
        $data = [
            'products' => [
                ['id' => 1, 'name' => 'Desk', 'category' => 'Office', 'sale' => true],
                ['id' => 2, 'name' => 'Plant', 'category' => 'Home', 'sale' => true],
                ['id' => 3, 'name' => 'Stapler', 'category' => 'Office', 'sale' => true],
                ['id' => 4, 'name' => 'Pen', 'category' => 'Office', 'sale' => false],
                ['id' => 5, 'name' => 'Table', 'category' => 'Home', 'sale' => false],
            ],
        ];

        $template = <<<'EOT'
{{ my_var = 0; }}{{ products }}{{ my_var += 1; }}{{ /products }}{{ my_var }}
EOT;
        $this->assertSame('5', $this->renderString($template, $data));
    }

    public function test_assignments_within_another_scope_do_not_leak_to_outer_scope()
    {
        $data = [
            'products' => [
                ['id' => 1, 'name' => 'Desk', 'category' => 'Office', 'sale' => true],
                ['id' => 2, 'name' => 'Plant', 'category' => 'Home', 'sale' => true],
                ['id' => 3, 'name' => 'Stapler', 'category' => 'Office', 'sale' => true],
                ['id' => 4, 'name' => 'Pen', 'category' => 'Office', 'sale' => false],
                ['id' => 5, 'name' => 'Table', 'category' => 'Home', 'sale' => false],
            ],
        ];

        $template = <<<'EOT'
{{ my_var = 0; }}{{ products }}{{ my_var += 1; }}{{ another_var += 1; another_var }}{{ /products }}<my_var {{ my_var }}><another_var: {{ another_var ?? 0 }}>
EOT;
        $this->assertSame('11111<my_var 5><another_var: 0>', $this->renderString($template, $data));
    }

    public function test_nested_arrays_can_be_summed()
    {
        Taxonomy::make('tags')->save();
        Collection::make('blog')->taxonomies(['tags'])->save();
        EntryFactory::collection('blog')->id('1')->data(['tags' => ['rad', 'test', 'test-two']])->create();
        EntryFactory::collection('blog')->id('2')->data(['tags' => ['rad', 'two']])->create();
        EntryFactory::collection('blog')->id('3')->data(['tags' => ['meh']])->create();
        EntryFactory::collection('blog')->id('4')->create();

        $template = <<<'EOT'
{{ my_count = 0 }}
{{ collection:blog }}
    {{ my_count += (tags|count) }}
{{ /collection:blog }}
{{ my_count }}
EOT;

        $this->assertSame('6', trim($this->renderString($template, [], true)));

        $template = <<<'EOT'
{{ $my_count = 0; }}
{{ collection:blog }}
    {{ $my_count += (tags|count) }}
{{ /collection:blog }}
{{ $my_count }}
EOT;

        $this->assertSame('6', trim($this->renderString($template, [], true)));
    }

    public function test_assignments_are_traced_from_nested_arrays_and_tags()
    {
        $data = [
            'root_data' => [
                'one',
                'two',
                'three',
            ],
            'other_thing' => [
                'thing_one',
                'thing_two',
                'thing_three',
            ],
            'another_thing' => [
                'another_one',
                'another_two',
                'another_three',
            ],
            'even_more' => [
                'more_one',
                'more_two',
                'more_three',
            ],
        ];

        // The 'prefixed' partial just returns its slot content as its only output.
        $template = <<<'EOT'
{{ data_count = 0 }}

{{ root_data }}
{{ value }}
{{ other_thing  }}
{{ value }}
{{ another_thing }}
{{ value }}
{{ even_more }}
{{ value }}
{{ data_count }}Adding {{ data_count += 1;}}
{{ partial:prefixed }}
{{ data_count }}Adding {{ data_count += 1;}}
{{ partial:prefixed }}
{{ data_count }}Adding {{ data_count += 1;}}
{{ partial:prefixed }}
{{ if true == true }}{{ data_count }}Adding {{ data_count += 1;}}{{ /if }}
{{ /partial:prefixed }}
{{ /partial:prefixed }}
{{ thing = 'that one thing'; }}
{{ /partial:prefixed }}
{{ data_count }}
{{ thing }}
{{ /even_more }}
{{ /another_thing }}
{{ /other_thing }}
Total: {{ data_count }}
{{ /root_data }}

Final: {{ data_count }}
EOT;

        $expected = <<<'EOT'
one

thing_one

another_one

more_one
0Adding 
1Adding 
2Adding 
3Adding
4


more_two
4Adding 
5Adding 
6Adding 
7Adding
8


more_three
8Adding 
9Adding 
10Adding 
11Adding
12



another_two

more_one
12Adding 
13Adding 
14Adding 
15Adding
16


more_two
16Adding 
17Adding 
18Adding 
19Adding
20


more_three
20Adding 
21Adding 
22Adding 
23Adding
24



another_three

more_one
24Adding 
25Adding 
26Adding 
27Adding
28


more_two
28Adding 
29Adding 
30Adding 
31Adding
32


more_three
32Adding 
33Adding 
34Adding 
35Adding
36




thing_two

another_one

more_one
36Adding 
37Adding 
38Adding 
39Adding
40


more_two
40Adding 
41Adding 
42Adding 
43Adding
44


more_three
44Adding 
45Adding 
46Adding 
47Adding
48



another_two

more_one
48Adding 
49Adding 
50Adding 
51Adding
52


more_two
52Adding 
53Adding 
54Adding 
55Adding
56


more_three
56Adding 
57Adding 
58Adding 
59Adding
60



another_three

more_one
60Adding 
61Adding 
62Adding 
63Adding
64


more_two
64Adding 
65Adding 
66Adding 
67Adding
68


more_three
68Adding 
69Adding 
70Adding 
71Adding
72




thing_three

another_one

more_one
72Adding 
73Adding 
74Adding 
75Adding
76


more_two
76Adding 
77Adding 
78Adding 
79Adding
80


more_three
80Adding 
81Adding 
82Adding 
83Adding
84



another_two

more_one
84Adding 
85Adding 
86Adding 
87Adding
88


more_two
88Adding 
89Adding 
90Adding 
91Adding
92


more_three
92Adding 
93Adding 
94Adding 
95Adding
96



another_three

more_one
96Adding 
97Adding 
98Adding 
99Adding
100


more_two
100Adding 
101Adding 
102Adding 
103Adding
104


more_three
104Adding 
105Adding 
106Adding 
107Adding
108




Total: 108

two

thing_one

another_one

more_one
108Adding 
109Adding 
110Adding 
111Adding
112


more_two
112Adding 
113Adding 
114Adding 
115Adding
116


more_three
116Adding 
117Adding 
118Adding 
119Adding
120



another_two

more_one
120Adding 
121Adding 
122Adding 
123Adding
124


more_two
124Adding 
125Adding 
126Adding 
127Adding
128


more_three
128Adding 
129Adding 
130Adding 
131Adding
132



another_three

more_one
132Adding 
133Adding 
134Adding 
135Adding
136


more_two
136Adding 
137Adding 
138Adding 
139Adding
140


more_three
140Adding 
141Adding 
142Adding 
143Adding
144




thing_two

another_one

more_one
144Adding 
145Adding 
146Adding 
147Adding
148


more_two
148Adding 
149Adding 
150Adding 
151Adding
152


more_three
152Adding 
153Adding 
154Adding 
155Adding
156



another_two

more_one
156Adding 
157Adding 
158Adding 
159Adding
160


more_two
160Adding 
161Adding 
162Adding 
163Adding
164


more_three
164Adding 
165Adding 
166Adding 
167Adding
168



another_three

more_one
168Adding 
169Adding 
170Adding 
171Adding
172


more_two
172Adding 
173Adding 
174Adding 
175Adding
176


more_three
176Adding 
177Adding 
178Adding 
179Adding
180




thing_three

another_one

more_one
180Adding 
181Adding 
182Adding 
183Adding
184


more_two
184Adding 
185Adding 
186Adding 
187Adding
188


more_three
188Adding 
189Adding 
190Adding 
191Adding
192



another_two

more_one
192Adding 
193Adding 
194Adding 
195Adding
196


more_two
196Adding 
197Adding 
198Adding 
199Adding
200


more_three
200Adding 
201Adding 
202Adding 
203Adding
204



another_three

more_one
204Adding 
205Adding 
206Adding 
207Adding
208


more_two
208Adding 
209Adding 
210Adding 
211Adding
212


more_three
212Adding 
213Adding 
214Adding 
215Adding
216




Total: 216

three

thing_one

another_one

more_one
216Adding 
217Adding 
218Adding 
219Adding
220


more_two
220Adding 
221Adding 
222Adding 
223Adding
224


more_three
224Adding 
225Adding 
226Adding 
227Adding
228



another_two

more_one
228Adding 
229Adding 
230Adding 
231Adding
232


more_two
232Adding 
233Adding 
234Adding 
235Adding
236


more_three
236Adding 
237Adding 
238Adding 
239Adding
240



another_three

more_one
240Adding 
241Adding 
242Adding 
243Adding
244


more_two
244Adding 
245Adding 
246Adding 
247Adding
248


more_three
248Adding 
249Adding 
250Adding 
251Adding
252




thing_two

another_one

more_one
252Adding 
253Adding 
254Adding 
255Adding
256


more_two
256Adding 
257Adding 
258Adding 
259Adding
260


more_three
260Adding 
261Adding 
262Adding 
263Adding
264



another_two

more_one
264Adding 
265Adding 
266Adding 
267Adding
268


more_two
268Adding 
269Adding 
270Adding 
271Adding
272


more_three
272Adding 
273Adding 
274Adding 
275Adding
276



another_three

more_one
276Adding 
277Adding 
278Adding 
279Adding
280


more_two
280Adding 
281Adding 
282Adding 
283Adding
284


more_three
284Adding 
285Adding 
286Adding 
287Adding
288




thing_three

another_one

more_one
288Adding 
289Adding 
290Adding 
291Adding
292


more_two
292Adding 
293Adding 
294Adding 
295Adding
296


more_three
296Adding 
297Adding 
298Adding 
299Adding
300



another_two

more_one
300Adding 
301Adding 
302Adding 
303Adding
304


more_two
304Adding 
305Adding 
306Adding 
307Adding
308


more_three
308Adding 
309Adding 
310Adding 
311Adding
312



another_three

more_one
312Adding 
313Adding 
314Adding 
315Adding
316


more_two
316Adding 
317Adding 
318Adding 
319Adding
320


more_three
320Adding 
321Adding 
322Adding 
323Adding
324




Total: 324


Final: 324
EOT;

        $results = trim($this->renderString($template, $data, true));
        $this->assertSame($expected, $results);
    }

    public function test_variable_assignment_do_not_leak()
    {
        $testTemplate = <<<'EOT'
{{ arg = arg ?? 'default' }}{{ arg }}
EOT;

        $this->withFakeViews();
        $this->viewShouldReturnRaw('test', $testTemplate);

        $template = <<<'EOT'
<{{ partial:test arg="foo" }}><{{ partial:test }}>
EOT;

        $this->assertSame('<foo><default>', trim($this->renderString($template)));
    }

    public function test_variable_assignments_are_not_reset_when_crossing_parser_boundaries()
    {
        $textTemplate = <<<'EOT'
<count:{{ counter }}>

{{ increment:something_else }}
{{ increment:something_else }}
{{ increment:something_else }}
{{ increment:something_else }}
{{ increment:something_else }}

{{ bard }}
{{ partial:echo }}{{ partial:echo }}{{ partial:echo }}{{ partial:echo }}{{ partial:echo }}{{ foreach:array_dynamic }}{{ /foreach:array_dynamic }}{{ /partial:echo }}{{ /partial:echo }}{{ /partial:echo }}{{ /partial:echo }}{{ /partial:echo }}
{{ /bard }}

{{ increment:something_else }}
EOT;

        $this->withFakeViews();
        $this->viewShouldReturnRaw('components.text', $textTemplate);
        $this->viewShouldReturnRaw('echo', '{{ slot }}');

        $this->runFieldTypeTest('replicator_blocks', null, ['bard']);
    }

    public function test_empty_arrays_can_be_created_and_pushed_to()
    {
        $template = <<<'EOT'
{{ empty_array = [] }}
{{ empty_array += 'One'; empty_array += 'Two'; }}
{{ empty_array += 'Three' }}
{{ empty_array += 'Four'; }}
{{ empty_array }}<{{ value }}>{{ /empty_array }}
EOT;

        $this->assertSame('<One><Two><Three><Four>', trim($this->renderString($template)));
    }

    public function test_arrays_with_data_can_be_created_and_pushed_to()
    {
        $template = <<<'EOT'
{{ the_array = ['Zero'] }}
{{ the_array += 'One'; the_array += 'Two'; }}
{{ the_array += 'Three' }}
{{ the_array += 'Four'; }}
{{ the_array }}<{{ value }}>{{ /the_array }}
EOT;

        $this->assertSame('<Zero><One><Two><Three><Four>', trim($this->renderString($template)));
    }

    public function test_updated_arrays_are_not_reset_by_tags()
    {
        Collection::make('pages')->routes(['en' => '{slug}'])->save();
        EntryFactory::collection('pages')->id('1')->slug('one')->data(['title' => 'One', 'template' => 'template'])->create();

        $this->withFakeViews();
        $this->viewShouldReturnRaw('layout', '{{ template_content }}');

        $template = <<<'EOT'
{{ the_array = ['One', 10, 30, 40, 50, 60]; }}
Value: {{ the_array.0 }}
{{ the_array.0 = 'Two'; }}
{{ the_array.1 *= 5; }}
{{ the_array.2 -= 5; }}
{{ the_array.3 %= 2; }}
{{ the_array.4 /= 5; }}
{{ the_array.5 += 10; }}
Value: {{ the_array.0 }}
{{ loop from="0" to="3" }}{{ index }}{{ /loop }}
Value0: {{ the_array.0 }}
Value1: {{ the_array.1 }}
Value2: {{ the_array.2 }}
Value3: {{ the_array.3 }}
Value4: {{ the_array.4 }}
Value5: {{ the_array.5 }}
EOT;

        $this->viewShouldReturnRaw('template', $template);

        $responseOne = $this->get('one')->assertOk();

        $expected = <<<'EXPECTED'
Value: One






Value: Two
0123
Value0: Two
Value1: 50
Value2: 25
Value3: 0
Value4: 10
Value5: 70
EXPECTED;

        $this->assertSame($expected, StringUtilities::normalizeLineEndings(trim($responseOne->getContent())));
    }

    public function test_assignments_are_processed_after_associative_arrays()
    {
        $template = <<<'EOT'
{{ _value = 'One'; }}

{{ data }}
    {{ _value = 'Two'; }}
{{ /data }}

The value: {{ _value }}.
EOT;

        $this->assertSame('The value: Two.', trim($this->renderString($template, ['data' => ['foo' => 'bar']])));
    }

    public function test_variable_assignment_and_tag_aliasing()
    {
        EntryFactory::collection('blog')->id('1')->data(['title' => '1-One'])->create();
        EntryFactory::collection('blog')->id('2')->data(['title' => '2-Two'])->create();
        EntryFactory::collection('blog')->id('3')->data(['title' => '3-Three'])->create();

        $template = <<<'EOT'
{{ entries = {collection:blog limit="1"} }}
A: {{ entries }}{{ title }}{{ /entries }}
{{ collection:blog as="entries" }}
B: {{ entries }}{{ title }}{{ /entries }}
{{ /collection:blog }}
C: {{ entries }}{{ title }}{{ /entries }}
EOT;

        $expected = <<<'EOT'
A: 1-One

B: 1-One2-Two3-Three

C: 1-One
EOT;

        $this->assertSame($expected, trim($this->renderString($template, [], true)));

        $template = <<<'EOT'
{{ entries = {collection:blog limit="1"} }}
A: {{ entries }}{{ title }}{{ /entries }}
{{ collection:blog as="entries" }}
B: {{ entries }}{{ title }}{{ /entries }}

{{ entries = [['title' => 'Five'], ['title' => 'Six']] /}}
{{ /collection:blog }}
C: {{ entries }}{{ title }}{{ /entries }}
EOT;

        $expected = <<<'EOT'
A: 1-One

B: 1-One2-Two3-Three



C: FiveSix
EOT;

        $this->assertSame($expected, trim($this->renderString($template, [], true)));
    }
}
