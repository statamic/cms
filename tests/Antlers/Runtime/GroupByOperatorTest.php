<?php

namespace Tests\Antlers\Runtime;

use Statamic\View\Antlers\Language\Utilities\StringUtilities;
use Tests\Antlers\ParserTestCase;

class GroupByOperatorTest extends ParserTestCase
{
    protected $groupByData = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->groupByData = [
            'products' => [
                ['id' => 1, 'name' => 'Desk', 'category' => 'Office', 'sale' => true],
                ['id' => 2, 'name' => 'Plant', 'category' => 'Home', 'sale' => true],
                ['id' => 3, 'name' => 'Stapler', 'category' => 'Office', 'sale' => true],
                ['id' => 4, 'name' => 'Pen', 'category' => 'Office', 'sale' => false],
                ['id' => 5, 'name' => 'Table', 'category' => 'Home', 'sale' => false],
            ],
        ];
    }

    public function test_groupby_with_single_custom_key_works()
    {
        $template = <<<'EOT'
{{ grouped = products groupby (category 'my_name') }}{{ my_name }}{{ values | length }}{{ /grouped }}
EOT;
        $this->assertSame('Office3Home2', $this->renderString($template, $this->groupByData));
    }

    public function test_basic_group_by_information()
    {
        $template = <<<'EOT'
{{ grouped = products groupby (category) }}{{ key }}{{ values | length }}{{ /grouped }}
EOT;
        $this->assertSame('Office3Home2', $this->renderString($template, $this->groupByData));

        $template = <<<'EOT'
{{ grouped = products groupby (category) }}{{ key }}{{ values_count }}{{ /grouped }}
EOT;
        $this->assertSame('Office3Home2', $this->renderString($template, $this->groupByData));
    }

    public function test_group_by_populates_array_data()
    {
        $expected = <<<'EOT'
==========================================
Key: Office
Value Count: 3
Calculated Count 3
Total Results: 2
First: 1
Last: 
Loop Count: 1
Loop Index: 0

===============ARRAY RESULT===============

ID: 1
Name: Desk
Category: Office
First: 1
Last: 
Loop Count: 1
Loop Index: 0

ID: 3
Name: Stapler
Category: Office
First: 
Last: 
Loop Count: 2
Loop Index: 1

ID: 4
Name: Pen
Category: Office
First: 
Last: 1
Loop Count: 3
Loop Index: 2


==============SCOPED RESULT===============

ID: 1
Name: Desk
Category: Office
First: 1
Last: 
Loop Count: 1
Loop Index: 0

ID: 3
Name: Stapler
Category: Office
First: 
Last: 
Loop Count: 2
Loop Index: 1

ID: 4
Name: Pen
Category: Office
First: 
Last: 1
Loop Count: 3
Loop Index: 2

==========================================

==========================================
Key: Home
Value Count: 2
Calculated Count 2
Total Results: 2
First: 
Last: 1
Loop Count: 2
Loop Index: 1

===============ARRAY RESULT===============

ID: 2
Name: Plant
Category: Home
First: 1
Last: 
Loop Count: 1
Loop Index: 0

ID: 5
Name: Table
Category: Home
First: 
Last: 1
Loop Count: 2
Loop Index: 1


==============SCOPED RESULT===============

ID: 2
Name: Plant
Category: Home
First: 1
Last: 
Loop Count: 1
Loop Index: 0

ID: 5
Name: Table
Category: Home
First: 
Last: 1
Loop Count: 2
Loop Index: 1

==========================================
EOT;

        $template = <<<'EOT'
{{ grouped = products groupby (category) }}
==========================================
Key: {{ key }}
Value Count: {{ values_count }}
Calculated Count {{ values | length }}
Total Results: {{ total_results }}
First: {{ first }}
Last: {{ last }}
Loop Count: {{ count }}
Loop Index: {{ index }}

===============ARRAY RESULT===============
{{ values }}
ID: {{ id }}
Name: {{ name }}
Category: {{ category }}
First: {{ first }}
Last: {{ last }}
Loop Count: {{ count }}
Loop Index: {{ index }}
{{ /values }}

==============SCOPED RESULT===============
{{ values scope="test" }}
ID: {{ test:id }}
Name: {{ test:name }}
Category: {{ test:category }}
First: {{ test:first }}
Last: {{ test:last }}
Loop Count: {{ test:count }}
Loop Index: {{ test:index }}
{{ /values }}
==========================================
{{ /grouped }}
EOT;

        $this->assertSame(
            trim(StringUtilities::normalizeLineEndings($expected)),
            trim(StringUtilities::normalizeLineEndings($this->renderString($template, $this->groupByData))));
    }

    public function test_group_by_with_multiple_group_conditions()
    {
        $template = <<<'EOT'
{{ grouped = products groupby (category, sale) }}
==========================================
Key1: {{ key:category }}
Key2: {{ key:sale }}
{{ if key:sale }}On Sale!{{ else }}No sale.{{ /if }}
Value Count: {{ values_count }}
Calculated Count {{ values | length }}
Total Results: {{ total_results }}
First: {{ first }}
Last: {{ last }}
Loop Count: {{ count }}
Loop Index: {{ index }}

===============ARRAY RESULT===============
{{ values }}
ID: {{ id }}
Name: {{ name }}
Category: {{ category }}
First: {{ first }}
Last: {{ last }}
Loop Count: {{ count }}
Loop Index: {{ index }}
{{ /values }}

==============SCOPED RESULT===============
{{ values scope="test" }}
ID: {{ test:id }}
Name: {{ test:name }}
Category: {{ test:category }}
First: {{ test:first }}
Last: {{ test:last }}
Loop Count: {{ test:count }}
Loop Index: {{ test:index }}
{{ /values }}
==========================================
{{ /grouped }}
EOT;

        $expected = <<<'EOT'
==========================================
Key1: Office
Key2: 1
On Sale!
Value Count: 2
Calculated Count 2
Total Results: 4
First: 1
Last: 
Loop Count: 1
Loop Index: 0

===============ARRAY RESULT===============

ID: 1
Name: Desk
Category: Office
First: 1
Last: 
Loop Count: 1
Loop Index: 0

ID: 3
Name: Stapler
Category: Office
First: 
Last: 1
Loop Count: 2
Loop Index: 1


==============SCOPED RESULT===============

ID: 1
Name: Desk
Category: Office
First: 1
Last: 
Loop Count: 1
Loop Index: 0

ID: 3
Name: Stapler
Category: Office
First: 
Last: 1
Loop Count: 2
Loop Index: 1

==========================================

==========================================
Key1: Home
Key2: 1
On Sale!
Value Count: 1
Calculated Count 1
Total Results: 4
First: 
Last: 
Loop Count: 2
Loop Index: 1

===============ARRAY RESULT===============

ID: 2
Name: Plant
Category: Home
First: 1
Last: 1
Loop Count: 1
Loop Index: 0


==============SCOPED RESULT===============

ID: 2
Name: Plant
Category: Home
First: 1
Last: 1
Loop Count: 1
Loop Index: 0

==========================================

==========================================
Key1: Office
Key2: 
No sale.
Value Count: 1
Calculated Count 1
Total Results: 4
First: 
Last: 
Loop Count: 3
Loop Index: 2

===============ARRAY RESULT===============

ID: 4
Name: Pen
Category: Office
First: 1
Last: 1
Loop Count: 1
Loop Index: 0


==============SCOPED RESULT===============

ID: 4
Name: Pen
Category: Office
First: 1
Last: 1
Loop Count: 1
Loop Index: 0

==========================================

==========================================
Key1: Home
Key2: 
No sale.
Value Count: 1
Calculated Count 1
Total Results: 4
First: 
Last: 1
Loop Count: 4
Loop Index: 3

===============ARRAY RESULT===============

ID: 5
Name: Table
Category: Home
First: 1
Last: 1
Loop Count: 1
Loop Index: 0


==============SCOPED RESULT===============

ID: 5
Name: Table
Category: Home
First: 1
Last: 1
Loop Count: 1
Loop Index: 0

==========================================
EOT;

        $this->assertSame(
            trim(StringUtilities::normalizeLineEndings($expected)),
            trim(StringUtilities::normalizeLineEndings($this->renderString($template, $this->groupByData))));
    }

    public function test_group_by_with_multiple_group_conditions_aliased()
    {
        $template = <<<'EOT'
{{ grouped = products groupby (category 'my_category', sale) }}
==========================================
Key1: {{ key:my_category }}
Key2: {{ key:sale }}
{{ if key:sale }}On Sale!{{ else }}No sale.{{ /if }}
Value Count: {{ values_count }}
Calculated Count {{ values | length }}
Total Results: {{ total_results }}
First: {{ first }}
Last: {{ last }}
Loop Count: {{ count }}
Loop Index: {{ index }}

===============ARRAY RESULT===============
{{ values }}
ID: {{ id }}
Name: {{ name }}
Category: {{ category }}
First: {{ first }}
Last: {{ last }}
Loop Count: {{ count }}
Loop Index: {{ index }}
{{ /values }}

==============SCOPED RESULT===============
{{ values scope="test" }}
ID: {{ test:id }}
Name: {{ test:name }}
Category: {{ test:category }}
First: {{ test:first }}
Last: {{ test:last }}
Loop Count: {{ test:count }}
Loop Index: {{ test:index }}
{{ /values }}
==========================================
{{ /grouped }}
EOT;

        $expected = <<<'EOT'
==========================================
Key1: Office
Key2: 1
On Sale!
Value Count: 2
Calculated Count 2
Total Results: 4
First: 1
Last: 
Loop Count: 1
Loop Index: 0

===============ARRAY RESULT===============

ID: 1
Name: Desk
Category: Office
First: 1
Last: 
Loop Count: 1
Loop Index: 0

ID: 3
Name: Stapler
Category: Office
First: 
Last: 1
Loop Count: 2
Loop Index: 1


==============SCOPED RESULT===============

ID: 1
Name: Desk
Category: Office
First: 1
Last: 
Loop Count: 1
Loop Index: 0

ID: 3
Name: Stapler
Category: Office
First: 
Last: 1
Loop Count: 2
Loop Index: 1

==========================================

==========================================
Key1: Home
Key2: 1
On Sale!
Value Count: 1
Calculated Count 1
Total Results: 4
First: 
Last: 
Loop Count: 2
Loop Index: 1

===============ARRAY RESULT===============

ID: 2
Name: Plant
Category: Home
First: 1
Last: 1
Loop Count: 1
Loop Index: 0


==============SCOPED RESULT===============

ID: 2
Name: Plant
Category: Home
First: 1
Last: 1
Loop Count: 1
Loop Index: 0

==========================================

==========================================
Key1: Office
Key2: 
No sale.
Value Count: 1
Calculated Count 1
Total Results: 4
First: 
Last: 
Loop Count: 3
Loop Index: 2

===============ARRAY RESULT===============

ID: 4
Name: Pen
Category: Office
First: 1
Last: 1
Loop Count: 1
Loop Index: 0


==============SCOPED RESULT===============

ID: 4
Name: Pen
Category: Office
First: 1
Last: 1
Loop Count: 1
Loop Index: 0

==========================================

==========================================
Key1: Home
Key2: 
No sale.
Value Count: 1
Calculated Count 1
Total Results: 4
First: 
Last: 1
Loop Count: 4
Loop Index: 3

===============ARRAY RESULT===============

ID: 5
Name: Table
Category: Home
First: 1
Last: 1
Loop Count: 1
Loop Index: 0


==============SCOPED RESULT===============

ID: 5
Name: Table
Category: Home
First: 1
Last: 1
Loop Count: 1
Loop Index: 0

==========================================
EOT;

        $this->assertSame(
            trim(StringUtilities::normalizeLineEndings($expected)),
            trim(StringUtilities::normalizeLineEndings($this->renderString($template, $this->groupByData))));
    }

    public function test_group_by_with_multiple_group_conditions_only_last_aliased()
    {
        $template = <<<'EOT'
{{ grouped = products groupby (category, sale 'my_sale') }}
==========================================
Key1: {{ key:category }}
Key2: {{ key:my_sale }}
{{ if key:my_sale }}On Sale!{{ else }}No sale.{{ /if }}
Value Count: {{ values_count }}
Calculated Count {{ values | length }}
Total Results: {{ total_results }}
First: {{ first }}
Last: {{ last }}
Loop Count: {{ count }}
Loop Index: {{ index }}

===============ARRAY RESULT===============
{{ values }}
ID: {{ id }}
Name: {{ name }}
Category: {{ category }}
First: {{ first }}
Last: {{ last }}
Loop Count: {{ count }}
Loop Index: {{ index }}
{{ /values }}

==============SCOPED RESULT===============
{{ values scope="test" }}
ID: {{ test:id }}
Name: {{ test:name }}
Category: {{ test:category }}
First: {{ test:first }}
Last: {{ test:last }}
Loop Count: {{ test:count }}
Loop Index: {{ test:index }}
{{ /values }}
==========================================
{{ /grouped }}
EOT;

        $expected = <<<'EOT'
==========================================
Key1: Office
Key2: 1
On Sale!
Value Count: 2
Calculated Count 2
Total Results: 4
First: 1
Last: 
Loop Count: 1
Loop Index: 0

===============ARRAY RESULT===============

ID: 1
Name: Desk
Category: Office
First: 1
Last: 
Loop Count: 1
Loop Index: 0

ID: 3
Name: Stapler
Category: Office
First: 
Last: 1
Loop Count: 2
Loop Index: 1


==============SCOPED RESULT===============

ID: 1
Name: Desk
Category: Office
First: 1
Last: 
Loop Count: 1
Loop Index: 0

ID: 3
Name: Stapler
Category: Office
First: 
Last: 1
Loop Count: 2
Loop Index: 1

==========================================

==========================================
Key1: Home
Key2: 1
On Sale!
Value Count: 1
Calculated Count 1
Total Results: 4
First: 
Last: 
Loop Count: 2
Loop Index: 1

===============ARRAY RESULT===============

ID: 2
Name: Plant
Category: Home
First: 1
Last: 1
Loop Count: 1
Loop Index: 0


==============SCOPED RESULT===============

ID: 2
Name: Plant
Category: Home
First: 1
Last: 1
Loop Count: 1
Loop Index: 0

==========================================

==========================================
Key1: Office
Key2: 
No sale.
Value Count: 1
Calculated Count 1
Total Results: 4
First: 
Last: 
Loop Count: 3
Loop Index: 2

===============ARRAY RESULT===============

ID: 4
Name: Pen
Category: Office
First: 1
Last: 1
Loop Count: 1
Loop Index: 0


==============SCOPED RESULT===============

ID: 4
Name: Pen
Category: Office
First: 1
Last: 1
Loop Count: 1
Loop Index: 0

==========================================

==========================================
Key1: Home
Key2: 
No sale.
Value Count: 1
Calculated Count 1
Total Results: 4
First: 
Last: 1
Loop Count: 4
Loop Index: 3

===============ARRAY RESULT===============

ID: 5
Name: Table
Category: Home
First: 1
Last: 1
Loop Count: 1
Loop Index: 0


==============SCOPED RESULT===============

ID: 5
Name: Table
Category: Home
First: 1
Last: 1
Loop Count: 1
Loop Index: 0

==========================================
EOT;

        $this->assertSame(
            trim(StringUtilities::normalizeLineEndings($expected)),
            trim(StringUtilities::normalizeLineEndings($this->renderString($template, $this->groupByData))));
    }

    public function test_group_by_with_multiple_group_both_conditions_aliased()
    {
        $template = <<<'EOT'
{{ grouped = products groupby (category 'my_category', sale 'my_sale') }}
==========================================
Key1: {{ key:my_category }}
Key2: {{ key:my_sale }}
{{ if key:my_sale }}On Sale!{{ else }}No sale.{{ /if }}
Value Count: {{ values_count }}
Calculated Count {{ values | length }}
Total Results: {{ total_results }}
First: {{ first }}
Last: {{ last }}
Loop Count: {{ count }}
Loop Index: {{ index }}

===============ARRAY RESULT===============
{{ values }}
ID: {{ id }}
Name: {{ name }}
Category: {{ category }}
First: {{ first }}
Last: {{ last }}
Loop Count: {{ count }}
Loop Index: {{ index }}
{{ /values }}

==============SCOPED RESULT===============
{{ values scope="test" }}
ID: {{ test:id }}
Name: {{ test:name }}
Category: {{ test:category }}
First: {{ test:first }}
Last: {{ test:last }}
Loop Count: {{ test:count }}
Loop Index: {{ test:index }}
{{ /values }}
==========================================
{{ /grouped }}
EOT;

        $expected = <<<'EOT'
==========================================
Key1: Office
Key2: 1
On Sale!
Value Count: 2
Calculated Count 2
Total Results: 4
First: 1
Last: 
Loop Count: 1
Loop Index: 0

===============ARRAY RESULT===============

ID: 1
Name: Desk
Category: Office
First: 1
Last: 
Loop Count: 1
Loop Index: 0

ID: 3
Name: Stapler
Category: Office
First: 
Last: 1
Loop Count: 2
Loop Index: 1


==============SCOPED RESULT===============

ID: 1
Name: Desk
Category: Office
First: 1
Last: 
Loop Count: 1
Loop Index: 0

ID: 3
Name: Stapler
Category: Office
First: 
Last: 1
Loop Count: 2
Loop Index: 1

==========================================

==========================================
Key1: Home
Key2: 1
On Sale!
Value Count: 1
Calculated Count 1
Total Results: 4
First: 
Last: 
Loop Count: 2
Loop Index: 1

===============ARRAY RESULT===============

ID: 2
Name: Plant
Category: Home
First: 1
Last: 1
Loop Count: 1
Loop Index: 0


==============SCOPED RESULT===============

ID: 2
Name: Plant
Category: Home
First: 1
Last: 1
Loop Count: 1
Loop Index: 0

==========================================

==========================================
Key1: Office
Key2: 
No sale.
Value Count: 1
Calculated Count 1
Total Results: 4
First: 
Last: 
Loop Count: 3
Loop Index: 2

===============ARRAY RESULT===============

ID: 4
Name: Pen
Category: Office
First: 1
Last: 1
Loop Count: 1
Loop Index: 0


==============SCOPED RESULT===============

ID: 4
Name: Pen
Category: Office
First: 1
Last: 1
Loop Count: 1
Loop Index: 0

==========================================

==========================================
Key1: Home
Key2: 
No sale.
Value Count: 1
Calculated Count 1
Total Results: 4
First: 
Last: 1
Loop Count: 4
Loop Index: 3

===============ARRAY RESULT===============

ID: 5
Name: Table
Category: Home
First: 1
Last: 1
Loop Count: 1
Loop Index: 0


==============SCOPED RESULT===============

ID: 5
Name: Table
Category: Home
First: 1
Last: 1
Loop Count: 1
Loop Index: 0

==========================================
EOT;

        $this->assertSame(
            trim(StringUtilities::normalizeLineEndings($expected)),
            trim(StringUtilities::normalizeLineEndings($this->renderString($template, $this->groupByData))));
    }

    public function test_group_by_with_renamed_group()
    {
        $template = <<<'EOT'
{{ grouped = products groupby (category) as 'my_group' }}
==========================================
Key1: {{ key }}
Value Count: {{ my_group_count }}
Calculated Count {{ my_group | length }}
Total Results: {{ total_results }}
First: {{ first }}
Last: {{ last }}
Loop Count: {{ count }}
Loop Index: {{ index }}

===============ARRAY RESULT===============
{{ my_group }}
ID: {{ id }}
Name: {{ name }}
Category: {{ category }}
First: {{ first }}
Last: {{ last }}
Loop Count: {{ count }}
Loop Index: {{ index }}
{{ /my_group }}

==============SCOPED RESULT===============
{{ my_group scope="test" }}
ID: {{ test:id }}
Name: {{ test:name }}
Category: {{ test:category }}
First: {{ test:first }}
Last: {{ test:last }}
Loop Count: {{ test:count }}
Loop Index: {{ test:index }}
{{ /my_group }}
==========================================
{{ /grouped }}
EOT;

        $expected = <<<'EOT'
==========================================
Key1: Office
Value Count: 3
Calculated Count 3
Total Results: 2
First: 1
Last: 
Loop Count: 1
Loop Index: 0

===============ARRAY RESULT===============

ID: 1
Name: Desk
Category: Office
First: 1
Last: 
Loop Count: 1
Loop Index: 0

ID: 3
Name: Stapler
Category: Office
First: 
Last: 
Loop Count: 2
Loop Index: 1

ID: 4
Name: Pen
Category: Office
First: 
Last: 1
Loop Count: 3
Loop Index: 2


==============SCOPED RESULT===============

ID: 1
Name: Desk
Category: Office
First: 1
Last: 
Loop Count: 1
Loop Index: 0

ID: 3
Name: Stapler
Category: Office
First: 
Last: 
Loop Count: 2
Loop Index: 1

ID: 4
Name: Pen
Category: Office
First: 
Last: 1
Loop Count: 3
Loop Index: 2

==========================================

==========================================
Key1: Home
Value Count: 2
Calculated Count 2
Total Results: 2
First: 
Last: 1
Loop Count: 2
Loop Index: 1

===============ARRAY RESULT===============

ID: 2
Name: Plant
Category: Home
First: 1
Last: 
Loop Count: 1
Loop Index: 0

ID: 5
Name: Table
Category: Home
First: 
Last: 1
Loop Count: 2
Loop Index: 1


==============SCOPED RESULT===============

ID: 2
Name: Plant
Category: Home
First: 1
Last: 
Loop Count: 1
Loop Index: 0

ID: 5
Name: Table
Category: Home
First: 
Last: 1
Loop Count: 2
Loop Index: 1

==========================================
EOT;

        $this->assertSame(
            trim(StringUtilities::normalizeLineEndings($expected)),
            trim(StringUtilities::normalizeLineEndings($this->renderString($template, $this->groupByData))));
    }

    public function test_group_by_with_aliased_logic_group()
    {
        $template = <<<'EOT'
{{ grouped = products groupby ((x => x:category)) as 'my_group' }}
==========================================
Key1: {{ key }}
Value Count: {{ my_group_count }}
Calculated Count {{ my_group | length }}
Total Results: {{ total_results }}
First: {{ first }}
Last: {{ last }}
Loop Count: {{ count }}
Loop Index: {{ index }}

===============ARRAY RESULT===============
{{ my_group }}
ID: {{ id }}
Name: {{ name }}
Category: {{ category }}
First: {{ first }}
Last: {{ last }}
Loop Count: {{ count }}
Loop Index: {{ index }}
{{ /my_group }}

==============SCOPED RESULT===============
{{ my_group scope="test" }}
ID: {{ test:id }}
Name: {{ test:name }}
Category: {{ test:category }}
First: {{ test:first }}
Last: {{ test:last }}
Loop Count: {{ test:count }}
Loop Index: {{ test:index }}
{{ /my_group }}
==========================================
{{ /grouped }}
EOT;

        $expected = <<<'EOT'
==========================================
Key1: Office
Value Count: 3
Calculated Count 3
Total Results: 2
First: 1
Last: 
Loop Count: 1
Loop Index: 0

===============ARRAY RESULT===============

ID: 1
Name: Desk
Category: Office
First: 1
Last: 
Loop Count: 1
Loop Index: 0

ID: 3
Name: Stapler
Category: Office
First: 
Last: 
Loop Count: 2
Loop Index: 1

ID: 4
Name: Pen
Category: Office
First: 
Last: 1
Loop Count: 3
Loop Index: 2


==============SCOPED RESULT===============

ID: 1
Name: Desk
Category: Office
First: 1
Last: 
Loop Count: 1
Loop Index: 0

ID: 3
Name: Stapler
Category: Office
First: 
Last: 
Loop Count: 2
Loop Index: 1

ID: 4
Name: Pen
Category: Office
First: 
Last: 1
Loop Count: 3
Loop Index: 2

==========================================

==========================================
Key1: Home
Value Count: 2
Calculated Count 2
Total Results: 2
First: 
Last: 1
Loop Count: 2
Loop Index: 1

===============ARRAY RESULT===============

ID: 2
Name: Plant
Category: Home
First: 1
Last: 
Loop Count: 1
Loop Index: 0

ID: 5
Name: Table
Category: Home
First: 
Last: 1
Loop Count: 2
Loop Index: 1


==============SCOPED RESULT===============

ID: 2
Name: Plant
Category: Home
First: 1
Last: 
Loop Count: 1
Loop Index: 0

ID: 5
Name: Table
Category: Home
First: 
Last: 1
Loop Count: 2
Loop Index: 1

==========================================
EOT;

        $this->assertSame(
            trim(StringUtilities::normalizeLineEndings($expected)),
            trim(StringUtilities::normalizeLineEndings($this->renderString($template, $this->groupByData))));
    }

    public function test_group_by_condition_with_custom_key_name()
    {
        $template = <<<'EOT'
{{ grouped = products groupby ((x => x:category) as 'custom_key_name') as 'my_group' }}
==========================================
Key1: {{ custom_key_name }}
Value Count: {{ my_group_count }}
Calculated Count {{ my_group | length }}
Total Results: {{ total_results }}
First: {{ first }}
Last: {{ last }}
Loop Count: {{ count }}
Loop Index: {{ index }}

===============ARRAY RESULT===============
{{ my_group }}
ID: {{ id }}
Name: {{ name }}
Category: {{ category }}
First: {{ first }}
Last: {{ last }}
Loop Count: {{ count }}
Loop Index: {{ index }}
{{ /my_group }}

==============SCOPED RESULT===============
{{ my_group scope="test" }}
ID: {{ test:id }}
Name: {{ test:name }}
Category: {{ test:category }}
First: {{ test:first }}
Last: {{ test:last }}
Loop Count: {{ test:count }}
Loop Index: {{ test:index }}
{{ /my_group }}
==========================================
{{ /grouped }}
EOT;

        $expected = <<<'EOT'
==========================================
Key1: Office
Value Count: 3
Calculated Count 3
Total Results: 2
First: 1
Last: 
Loop Count: 1
Loop Index: 0

===============ARRAY RESULT===============

ID: 1
Name: Desk
Category: Office
First: 1
Last: 
Loop Count: 1
Loop Index: 0

ID: 3
Name: Stapler
Category: Office
First: 
Last: 
Loop Count: 2
Loop Index: 1

ID: 4
Name: Pen
Category: Office
First: 
Last: 1
Loop Count: 3
Loop Index: 2


==============SCOPED RESULT===============

ID: 1
Name: Desk
Category: Office
First: 1
Last: 
Loop Count: 1
Loop Index: 0

ID: 3
Name: Stapler
Category: Office
First: 
Last: 
Loop Count: 2
Loop Index: 1

ID: 4
Name: Pen
Category: Office
First: 
Last: 1
Loop Count: 3
Loop Index: 2

==========================================

==========================================
Key1: Home
Value Count: 2
Calculated Count 2
Total Results: 2
First: 
Last: 1
Loop Count: 2
Loop Index: 1

===============ARRAY RESULT===============

ID: 2
Name: Plant
Category: Home
First: 1
Last: 
Loop Count: 1
Loop Index: 0

ID: 5
Name: Table
Category: Home
First: 
Last: 1
Loop Count: 2
Loop Index: 1


==============SCOPED RESULT===============

ID: 2
Name: Plant
Category: Home
First: 1
Last: 
Loop Count: 1
Loop Index: 0

ID: 5
Name: Table
Category: Home
First: 
Last: 1
Loop Count: 2
Loop Index: 1

==========================================
EOT;

        $this->assertSame(
            trim(StringUtilities::normalizeLineEndings($expected)),
            trim(StringUtilities::normalizeLineEndings($this->renderString($template, $this->groupByData))));
    }

    public function test_multiple_group_conditions_with_custom_key_name()
    {
        $template = <<<'EOT'
{{ grouped = products groupby ((x => x:category) as 'custom_key_name', (x => x.sale)) as 'my_group' }}
==========================================
Key1: {{ custom_key_name }}
Key2: {{ key:sale }}
{{ if key:sale }}On Sale!{{ else }}No sale.{{ /if }}
Value Count: {{ my_group_count }}
Calculated Count {{ my_group | length }}
Total Results: {{ total_results }}
First: {{ first }}
Last: {{ last }}
Loop Count: {{ count }}
Loop Index: {{ index }}

===============ARRAY RESULT===============
{{ my_group }}
ID: {{ id }}
Name: {{ name }}
Category: {{ category }}
First: {{ first }}
Last: {{ last }}
Loop Count: {{ count }}
Loop Index: {{ index }}
{{ /my_group }}

==============SCOPED RESULT===============
{{ my_group scope="test" }}
ID: {{ test:id }}
Name: {{ test:name }}
Category: {{ test:category }}
First: {{ test:first }}
Last: {{ test:last }}
Loop Count: {{ test:count }}
Loop Index: {{ test:index }}
{{ /my_group }}
==========================================
{{ /grouped }}
EOT;

        $expected = <<<'EOT'
==========================================
Key1: 
Key2: 1
On Sale!
Value Count: 2
Calculated Count 2
Total Results: 4
First: 1
Last: 
Loop Count: 1
Loop Index: 0

===============ARRAY RESULT===============

ID: 1
Name: Desk
Category: Office
First: 1
Last: 
Loop Count: 1
Loop Index: 0

ID: 3
Name: Stapler
Category: Office
First: 
Last: 1
Loop Count: 2
Loop Index: 1


==============SCOPED RESULT===============

ID: 1
Name: Desk
Category: Office
First: 1
Last: 
Loop Count: 1
Loop Index: 0

ID: 3
Name: Stapler
Category: Office
First: 
Last: 1
Loop Count: 2
Loop Index: 1

==========================================

==========================================
Key1: 
Key2: 1
On Sale!
Value Count: 1
Calculated Count 1
Total Results: 4
First: 
Last: 
Loop Count: 2
Loop Index: 1

===============ARRAY RESULT===============

ID: 2
Name: Plant
Category: Home
First: 1
Last: 1
Loop Count: 1
Loop Index: 0


==============SCOPED RESULT===============

ID: 2
Name: Plant
Category: Home
First: 1
Last: 1
Loop Count: 1
Loop Index: 0

==========================================

==========================================
Key1: 
Key2: 
No sale.
Value Count: 1
Calculated Count 1
Total Results: 4
First: 
Last: 
Loop Count: 3
Loop Index: 2

===============ARRAY RESULT===============

ID: 4
Name: Pen
Category: Office
First: 1
Last: 1
Loop Count: 1
Loop Index: 0


==============SCOPED RESULT===============

ID: 4
Name: Pen
Category: Office
First: 1
Last: 1
Loop Count: 1
Loop Index: 0

==========================================

==========================================
Key1: 
Key2: 
No sale.
Value Count: 1
Calculated Count 1
Total Results: 4
First: 
Last: 1
Loop Count: 4
Loop Index: 3

===============ARRAY RESULT===============

ID: 5
Name: Table
Category: Home
First: 1
Last: 1
Loop Count: 1
Loop Index: 0


==============SCOPED RESULT===============

ID: 5
Name: Table
Category: Home
First: 1
Last: 1
Loop Count: 1
Loop Index: 0

==========================================
EOT;

        $this->assertSame(
            trim(StringUtilities::normalizeLineEndings($expected)),
            trim(StringUtilities::normalizeLineEndings($this->renderString($template, $this->groupByData))));
    }
}
