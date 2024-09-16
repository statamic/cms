<?php

namespace Tests\Antlers\Runtime;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Nav;
use Statamic\View\Antlers\Language\Runtime\GlobalRuntimeState;
use Statamic\View\Antlers\Language\Runtime\NodeProcessor;
use Statamic\View\Antlers\Language\Utilities\StringUtilities;
use Tests\Antlers\ParserTestCase;
use Tests\FakesViews;
use Tests\PreventSavingStacheItemsToDisk;

class RecursiveNodesTest extends ParserTestCase
{
    use FakesViews,
        PreventSavingStacheItemsToDisk;

    private function makeNavTree()
    {
        $tree = [
            ['id' => 'home', 'title' => 'Home', 'url' => '/'],
            [
                'id' => 'about', 'title' => 'About', 'url' => 'about',
                'children' => [
                    ['id' => 'team', 'title' => 'Team', 'url' => 'team'],
                    ['id' => 'leadership', 'title' => 'Leadership', 'url' => 'leadership'],
                ],
            ],
            [
                'id' => 'projects', 'title' => 'Projects', 'url' => 'projects',
                'children' => [
                    ['id' => 'project-1', 'title' => 'Project-1', 'url' => 'project-1'],
                    [
                        'id' => 'project-2', 'title' => 'Project-2', 'url' => 'project-2',
                        'children' => [
                            ['id' => 'project-2-nested', 'title' => 'Project 2 Nested', 'url' => 'project-2-nested'],
                        ],
                    ],
                ],
            ],
            ['id' => 'contact', 'title' => 'Contact', 'url' => 'contact'],
        ];

        $nav = Nav::make('main');
        $nav->makeTree('en', $tree)->save();
        $nav->save();
    }

    public function test_recursive_nodes_dont_reset_towards_the_end()
    {
        $tree = [];

        for ($i = 0; $i < 2; $i++) {
            $tree[] = [
                'id' => 'home'.$i, 'title' => 'Home.'.$i, 'url' => '/'.$i,
                'children' => [
                    ['id' => 'about'.$i, 'title' => 'About.'.$i, 'url' => 'about'.$i],
                    [
                        'id' => 'projects'.$i, 'title' => 'Projects.'.$i, 'url' => 'projects'.$i,
                        'children' => [
                            ['id' => 'project-1'.$i, 'title' => 'Project-1.'.$i, 'url' => 'project-1'.$i],

                            [
                                'id' => 'project-2'.$i, 'title' => 'Project-2.'.$i, 'url' => 'project-2'.$i,
                                'children' => [
                                    [
                                        'id' => 'project-2-nested'.$i, 'title' => 'Project 2 Nested.'.$i, 'url' => 'project-2-nested'.$i,
                                        'children' => [
                                            ['id' => 'project-2-nested-2'.$i, 'title' => 'Project 2 Nested 2.'.$i, 'url' => 'project-2-nested-2'.$i],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    ['id' => 'contact'.$i, 'title' => 'Contact.'.$i, 'url' => 'contact'.$i],
                ],
            ];
            $tree[] = ['id' => 'root-2'.$i, 'title' => 'Root-2.'.$i, 'url' => '/root-2'.$i];
            $tree[] = ['id' => 'root-3'.$i, 'title' => 'Root-3.'.$i, 'url' => '/root-3'.$i];
            $tree[] = ['id' => 'root-4'.$i, 'title' => 'Root-4.'.$i, 'url' => '/root-4'.$i];
            $tree[] = [
                'id' => 'fin-child-1.'.$i, 'title' => 'Fin Child 1.'.$i, 'url' => '/fin-child-1'.$i,
                'children' => [
                    ['id' => 'fin-child-2.'.$i, 'title' => 'Fin Child 2.'.$i, 'url' => '/fin-child-2'.$i],
                    ['id' => 'fin-child-3.'.$i, 'title' => 'Fin Child 3.'.$i, 'url' => '/fin-child-3'.$i],
                ],
            ];
            $tree[] = [
                'id' => 'fin-child-1b.'.$i, 'title' => 'Fin Child B 1.'.$i, 'url' => '/fin-child-1'.$i,
                'children' => [
                    ['id' => 'fin-child-2b.'.$i, 'title' => 'Fin Child B 2.'.$i, 'url' => '/fin-child-b-2'.$i],
                    [
                        'id' => 'fin-child-3b.'.$i, 'title' => 'Fin Child B 3.'.$i, 'url' => '/fin-child-b-3'.$i,
                        'children' => [
                            [
                                'id' => 'fin-child-3b-2.'.$i, 'title' => 'Fin Child B 3 2.'.$i, 'url' => '/fin-child-b-3-2'.$i,
                                'children' => [
                                    [
                                        'id' => 'fin-child-3b-2-2.'.$i, 'title' => 'Fin Child B 3 2 2.'.$i, 'url' => '/fin-child-b-3-2-2'.$i,
                                        'children' => [
                                            [
                                                'id' => 'fin-child-3b-2-2-2.'.$i, 'title' => 'Fin Child B 3 2 2 2.'.$i, 'url' => '/fin-child-b-3-2-2-2'.$i,
                                                'children' => [
                                                    ['id' => 'fin-child-3b-2-2-2-2.'.$i, 'title' => 'Fin Child B 3 2 2 2 2.'.$i, 'url' => '/fin-child-b-3-2-2-2-2'.$i],
                                                    ['id' => 'fin-child-3b-2-2-2-3.'.$i, 'title' => 'Fin Child B 3 2 2 2 3.'.$i, 'url' => '/fin-child-b-3-2-2-2-3'.$i],
                                                    ['id' => 'fin-child-3b-2-2-2-4.'.$i, 'title' => 'Fin Child B 3 2 2 2 4.'.$i, 'url' => '/fin-child-b-3-2-2-2-4'.$i],
                                                    ['id' => 'fin-child-3b-2-2-2-5.'.$i, 'title' => 'Fin Child B 3 2 2 2 5.'.$i, 'url' => '/fin-child-b-3-2-2-2-5'.$i],
                                                    ['id' => 'fin-child-3b-2-2-2-6.'.$i, 'title' => 'Fin Child B 3 2 2 2 6.'.$i, 'url' => '/fin-child-b-3-2-2-2-6'.$i],
                                                    ['id' => 'fin-child-3b-2-2-2-7.'.$i, 'title' => 'Fin Child B 3 2 2 2 7.'.$i, 'url' => '/fin-child-b-3-2-2-2-7'.$i],
                                                    ['id' => 'fin-child-3b-2-2-2-8.'.$i, 'title' => 'Fin Child B 3 2 2 2 8.'.$i, 'url' => '/fin-child-b-3-2-2-2-8'.$i],
                                                    ['id' => 'fin-child-3b-2-2-2-9.'.$i, 'title' => 'Fin Child B 3 2 2 2 9.'.$i, 'url' => '/fin-child-b-3-2-2-2-9'.$i],
                                                    ['id' => 'fin-child-3b-2-2-2-10.'.$i, 'title' => 'Fin Child B 3 2 2 2 10.'.$i, 'url' => '/fin-child-b-3-2-2-2-10'.$i],
                                                    [
                                                        'id' => 'fin-child-3b-2-2-2-11.'.$i, 'title' => 'Fin Child B 3 2 2 2 11.'.$i, 'url' => '/fin-child-b-3-2-2-2-11'.$i,
                                                        'children' => [
                                                            ['id' => 'fin-child-3b-2-2-2-11-2.'.$i, 'title' => 'Fin Child B 3 2 2 2 11 2.'.$i, 'url' => '/fin-child-b-3-2-2-2-11-2'.$i],
                                                            ['id' => 'fin-child-3b-2-2-2-11-3.'.$i, 'title' => 'Fin Child B 3 2 2 2 11 3.'.$i, 'url' => '/fin-child-b-3-2-2-2-11-3'.$i],
                                                            ['id' => 'fin-child-3b-2-2-2-11-4.'.$i, 'title' => 'Fin Child B 3 2 2 2 11 4.'.$i, 'url' => '/fin-child-b-3-2-2-2-11-4'.$i],
                                                            ['id' => 'fin-child-3b-2-2-2-11-5.'.$i, 'title' => 'Fin Child B 3 2 2 2 11 5.'.$i, 'url' => '/fin-child-b-3-2-2-2-11-5'.$i],
                                                            ['id' => 'fin-child-3b-2-2-2-11-6.'.$i, 'title' => 'Fin Child B 3 2 2 2 11 6.'.$i, 'url' => '/fin-child-b-3-2-2-2-11-6'.$i],
                                                        ],
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],

                            ],
                            ['id' => 'fin-child-3b-2-c.'.$i, 'title' => 'Fin B C3 2.'.$i, 'url' => '/fin-child-b-c-3-2'.$i],
                        ],
                    ],
                ],
            ];
            $tree[] = ['id' => 'tree-finish'.$i, 'title' => 'Tree-Finish.'.$i, 'url' => '/tree-finish'.$i];
        }

        $nav = Nav::make('main');
        $nav->makeTree('en', $tree)->save();
        $nav->save();

        $runtimeTree = [];

        // This test uses the {{ ___internal_debug:peek }} tag to help build up an array
        // based on the current state of the recursive nav tree. We will use this
        // output later to verify that the tree is built correctly, otherwise the
        // HTML output of this navigation tree is just insane.
        GlobalRuntimeState::$peekCallbacks = [];
        GlobalRuntimeState::$peekCallbacks[] = function (NodeProcessor $processor) use (&$runtimeTree) {
            $activeData = $processor->getActiveData();
            $curDepth = $activeData['depth'];
            $title = $activeData['title'];
            $branch = $activeData['branch'];
            $childDepth = null;

            if (array_key_exists('children_depth', $activeData)) {
                $childDepth = $activeData['children_depth'];
            }

            $runtimeTree[] = str_repeat(' ', $curDepth - 1).'depth: '.$curDepth.', title: '.$title.', childDepth: '.$childDepth.', branch: '.$branch;
        };

        $navTemplate = <<<'EOT'
{{ nav:main include_home="true" }}
{{ if depth == 1 }}
<li class="depth-1">
    {{ title }}
    {{ branch = 'depth==1' }}
    {{ ___internal_debug:peek }}
    {{ if children }}
    <ul>{{ *recursive children* }}</ul>
    {{ /if }}
</li>
{{ elseif depth == 2 }}
<li class="depth-2">
    {{ title }}
    {{ branch = 'depth==2' }}
    {{ ___internal_debug:peek }}
    {{ if children }}
    <ul>{{ *recursive children* }}</ul>
    {{ /if }}
</li>
{{ else }}
<li class="other-depth">
    {{ title }}
    {{ branch = 'else' }}
    {{ ___internal_debug:peek }}
    {{ if children }}
    <ul>{{ *recursive children* }}</ul>
    {{ /if }}
</li>
{{ /if }}
{{ /nav:main }}

{{# Ensure that re-used nodes get their initial depth reset correctly. #}}
{{ loop from="1" to="2" }}
{{ nav:main include_home="true" }}
{{ if depth == 1 }}
<li class="depth-1">
    {{ title }}
    {{ branch = 'depth==1' }}
    {{ ___internal_debug:peek }}
    {{ if children }}
    <ul>{{ *recursive children* }}</ul>
    {{ /if }}
</li>
{{ elseif depth == 2 }}
<li class="depth-2">
    {{ title }}
    {{ branch = 'depth==2' }}
    {{ ___internal_debug:peek }}
    {{ if children }}
    <ul>{{ *recursive children* }}</ul>
    {{ /if }}
</li>
{{ else }}
<li class="other-depth">
    {{ title }}
    {{ branch = 'else' }}
    {{ ___internal_debug:peek }}
    {{ if children }}
    <ul>{{ *recursive children* }}</ul>
    {{ /if }}
</li>
{{ /if }}
{{ /nav:main }}
{{ /loop }}
EOT;
        $this->renderString($navTemplate, [], true);

        $result = implode("\n", $runtimeTree);

        $expected = <<<'EOT'
depth: 1, title: Home.0, childDepth: , branch: depth==1
 depth: 2, title: About.0, childDepth: 2, branch: depth==2
 depth: 2, title: Projects.0, childDepth: 2, branch: depth==2
  depth: 3, title: Project-1.0, childDepth: 3, branch: else
  depth: 3, title: Project-2.0, childDepth: 3, branch: else
   depth: 4, title: Project 2 Nested.0, childDepth: 4, branch: else
    depth: 5, title: Project 2 Nested 2.0, childDepth: 5, branch: else
 depth: 2, title: Contact.0, childDepth: 2, branch: depth==2
depth: 1, title: Root-2.0, childDepth: , branch: depth==1
depth: 1, title: Root-3.0, childDepth: , branch: depth==1
depth: 1, title: Root-4.0, childDepth: , branch: depth==1
depth: 1, title: Fin Child 1.0, childDepth: , branch: depth==1
 depth: 2, title: Fin Child 2.0, childDepth: 2, branch: depth==2
 depth: 2, title: Fin Child 3.0, childDepth: 2, branch: depth==2
depth: 1, title: Fin Child B 1.0, childDepth: , branch: depth==1
 depth: 2, title: Fin Child B 2.0, childDepth: 2, branch: depth==2
 depth: 2, title: Fin Child B 3.0, childDepth: 2, branch: depth==2
  depth: 3, title: Fin Child B 3 2.0, childDepth: 3, branch: else
   depth: 4, title: Fin Child B 3 2 2.0, childDepth: 4, branch: else
    depth: 5, title: Fin Child B 3 2 2 2.0, childDepth: 5, branch: else
     depth: 6, title: Fin Child B 3 2 2 2 2.0, childDepth: 6, branch: else
     depth: 6, title: Fin Child B 3 2 2 2 3.0, childDepth: 6, branch: else
     depth: 6, title: Fin Child B 3 2 2 2 4.0, childDepth: 6, branch: else
     depth: 6, title: Fin Child B 3 2 2 2 5.0, childDepth: 6, branch: else
     depth: 6, title: Fin Child B 3 2 2 2 6.0, childDepth: 6, branch: else
     depth: 6, title: Fin Child B 3 2 2 2 7.0, childDepth: 6, branch: else
     depth: 6, title: Fin Child B 3 2 2 2 8.0, childDepth: 6, branch: else
     depth: 6, title: Fin Child B 3 2 2 2 9.0, childDepth: 6, branch: else
     depth: 6, title: Fin Child B 3 2 2 2 10.0, childDepth: 6, branch: else
     depth: 6, title: Fin Child B 3 2 2 2 11.0, childDepth: 6, branch: else
      depth: 7, title: Fin Child B 3 2 2 2 11 2.0, childDepth: 7, branch: else
      depth: 7, title: Fin Child B 3 2 2 2 11 3.0, childDepth: 7, branch: else
      depth: 7, title: Fin Child B 3 2 2 2 11 4.0, childDepth: 7, branch: else
      depth: 7, title: Fin Child B 3 2 2 2 11 5.0, childDepth: 7, branch: else
      depth: 7, title: Fin Child B 3 2 2 2 11 6.0, childDepth: 7, branch: else
  depth: 3, title: Fin B C3 2.0, childDepth: 3, branch: else
depth: 1, title: Tree-Finish.0, childDepth: , branch: depth==1
depth: 1, title: Home.1, childDepth: , branch: depth==1
 depth: 2, title: About.1, childDepth: 2, branch: depth==2
 depth: 2, title: Projects.1, childDepth: 2, branch: depth==2
  depth: 3, title: Project-1.1, childDepth: 3, branch: else
  depth: 3, title: Project-2.1, childDepth: 3, branch: else
   depth: 4, title: Project 2 Nested.1, childDepth: 4, branch: else
    depth: 5, title: Project 2 Nested 2.1, childDepth: 5, branch: else
 depth: 2, title: Contact.1, childDepth: 2, branch: depth==2
depth: 1, title: Root-2.1, childDepth: , branch: depth==1
depth: 1, title: Root-3.1, childDepth: , branch: depth==1
depth: 1, title: Root-4.1, childDepth: , branch: depth==1
depth: 1, title: Fin Child 1.1, childDepth: , branch: depth==1
 depth: 2, title: Fin Child 2.1, childDepth: 2, branch: depth==2
 depth: 2, title: Fin Child 3.1, childDepth: 2, branch: depth==2
depth: 1, title: Fin Child B 1.1, childDepth: , branch: depth==1
 depth: 2, title: Fin Child B 2.1, childDepth: 2, branch: depth==2
 depth: 2, title: Fin Child B 3.1, childDepth: 2, branch: depth==2
  depth: 3, title: Fin Child B 3 2.1, childDepth: 3, branch: else
   depth: 4, title: Fin Child B 3 2 2.1, childDepth: 4, branch: else
    depth: 5, title: Fin Child B 3 2 2 2.1, childDepth: 5, branch: else
     depth: 6, title: Fin Child B 3 2 2 2 2.1, childDepth: 6, branch: else
     depth: 6, title: Fin Child B 3 2 2 2 3.1, childDepth: 6, branch: else
     depth: 6, title: Fin Child B 3 2 2 2 4.1, childDepth: 6, branch: else
     depth: 6, title: Fin Child B 3 2 2 2 5.1, childDepth: 6, branch: else
     depth: 6, title: Fin Child B 3 2 2 2 6.1, childDepth: 6, branch: else
     depth: 6, title: Fin Child B 3 2 2 2 7.1, childDepth: 6, branch: else
     depth: 6, title: Fin Child B 3 2 2 2 8.1, childDepth: 6, branch: else
     depth: 6, title: Fin Child B 3 2 2 2 9.1, childDepth: 6, branch: else
     depth: 6, title: Fin Child B 3 2 2 2 10.1, childDepth: 6, branch: else
     depth: 6, title: Fin Child B 3 2 2 2 11.1, childDepth: 6, branch: else
      depth: 7, title: Fin Child B 3 2 2 2 11 2.1, childDepth: 7, branch: else
      depth: 7, title: Fin Child B 3 2 2 2 11 3.1, childDepth: 7, branch: else
      depth: 7, title: Fin Child B 3 2 2 2 11 4.1, childDepth: 7, branch: else
      depth: 7, title: Fin Child B 3 2 2 2 11 5.1, childDepth: 7, branch: else
      depth: 7, title: Fin Child B 3 2 2 2 11 6.1, childDepth: 7, branch: else
  depth: 3, title: Fin B C3 2.1, childDepth: 3, branch: else
depth: 1, title: Tree-Finish.1, childDepth: , branch: depth==1
depth: 1, title: Home.0, childDepth: , branch: depth==1
 depth: 2, title: About.0, childDepth: 2, branch: depth==2
 depth: 2, title: Projects.0, childDepth: 2, branch: depth==2
  depth: 3, title: Project-1.0, childDepth: 3, branch: else
  depth: 3, title: Project-2.0, childDepth: 3, branch: else
   depth: 4, title: Project 2 Nested.0, childDepth: 4, branch: else
    depth: 5, title: Project 2 Nested 2.0, childDepth: 5, branch: else
 depth: 2, title: Contact.0, childDepth: 2, branch: depth==2
depth: 1, title: Root-2.0, childDepth: , branch: depth==1
depth: 1, title: Root-3.0, childDepth: , branch: depth==1
depth: 1, title: Root-4.0, childDepth: , branch: depth==1
depth: 1, title: Fin Child 1.0, childDepth: , branch: depth==1
 depth: 2, title: Fin Child 2.0, childDepth: 2, branch: depth==2
 depth: 2, title: Fin Child 3.0, childDepth: 2, branch: depth==2
depth: 1, title: Fin Child B 1.0, childDepth: , branch: depth==1
 depth: 2, title: Fin Child B 2.0, childDepth: 2, branch: depth==2
 depth: 2, title: Fin Child B 3.0, childDepth: 2, branch: depth==2
  depth: 3, title: Fin Child B 3 2.0, childDepth: 3, branch: else
   depth: 4, title: Fin Child B 3 2 2.0, childDepth: 4, branch: else
    depth: 5, title: Fin Child B 3 2 2 2.0, childDepth: 5, branch: else
     depth: 6, title: Fin Child B 3 2 2 2 2.0, childDepth: 6, branch: else
     depth: 6, title: Fin Child B 3 2 2 2 3.0, childDepth: 6, branch: else
     depth: 6, title: Fin Child B 3 2 2 2 4.0, childDepth: 6, branch: else
     depth: 6, title: Fin Child B 3 2 2 2 5.0, childDepth: 6, branch: else
     depth: 6, title: Fin Child B 3 2 2 2 6.0, childDepth: 6, branch: else
     depth: 6, title: Fin Child B 3 2 2 2 7.0, childDepth: 6, branch: else
     depth: 6, title: Fin Child B 3 2 2 2 8.0, childDepth: 6, branch: else
     depth: 6, title: Fin Child B 3 2 2 2 9.0, childDepth: 6, branch: else
     depth: 6, title: Fin Child B 3 2 2 2 10.0, childDepth: 6, branch: else
     depth: 6, title: Fin Child B 3 2 2 2 11.0, childDepth: 6, branch: else
      depth: 7, title: Fin Child B 3 2 2 2 11 2.0, childDepth: 7, branch: else
      depth: 7, title: Fin Child B 3 2 2 2 11 3.0, childDepth: 7, branch: else
      depth: 7, title: Fin Child B 3 2 2 2 11 4.0, childDepth: 7, branch: else
      depth: 7, title: Fin Child B 3 2 2 2 11 5.0, childDepth: 7, branch: else
      depth: 7, title: Fin Child B 3 2 2 2 11 6.0, childDepth: 7, branch: else
  depth: 3, title: Fin B C3 2.0, childDepth: 3, branch: else
depth: 1, title: Tree-Finish.0, childDepth: , branch: depth==1
depth: 1, title: Home.1, childDepth: , branch: depth==1
 depth: 2, title: About.1, childDepth: 2, branch: depth==2
 depth: 2, title: Projects.1, childDepth: 2, branch: depth==2
  depth: 3, title: Project-1.1, childDepth: 3, branch: else
  depth: 3, title: Project-2.1, childDepth: 3, branch: else
   depth: 4, title: Project 2 Nested.1, childDepth: 4, branch: else
    depth: 5, title: Project 2 Nested 2.1, childDepth: 5, branch: else
 depth: 2, title: Contact.1, childDepth: 2, branch: depth==2
depth: 1, title: Root-2.1, childDepth: , branch: depth==1
depth: 1, title: Root-3.1, childDepth: , branch: depth==1
depth: 1, title: Root-4.1, childDepth: , branch: depth==1
depth: 1, title: Fin Child 1.1, childDepth: , branch: depth==1
 depth: 2, title: Fin Child 2.1, childDepth: 2, branch: depth==2
 depth: 2, title: Fin Child 3.1, childDepth: 2, branch: depth==2
depth: 1, title: Fin Child B 1.1, childDepth: , branch: depth==1
 depth: 2, title: Fin Child B 2.1, childDepth: 2, branch: depth==2
 depth: 2, title: Fin Child B 3.1, childDepth: 2, branch: depth==2
  depth: 3, title: Fin Child B 3 2.1, childDepth: 3, branch: else
   depth: 4, title: Fin Child B 3 2 2.1, childDepth: 4, branch: else
    depth: 5, title: Fin Child B 3 2 2 2.1, childDepth: 5, branch: else
     depth: 6, title: Fin Child B 3 2 2 2 2.1, childDepth: 6, branch: else
     depth: 6, title: Fin Child B 3 2 2 2 3.1, childDepth: 6, branch: else
     depth: 6, title: Fin Child B 3 2 2 2 4.1, childDepth: 6, branch: else
     depth: 6, title: Fin Child B 3 2 2 2 5.1, childDepth: 6, branch: else
     depth: 6, title: Fin Child B 3 2 2 2 6.1, childDepth: 6, branch: else
     depth: 6, title: Fin Child B 3 2 2 2 7.1, childDepth: 6, branch: else
     depth: 6, title: Fin Child B 3 2 2 2 8.1, childDepth: 6, branch: else
     depth: 6, title: Fin Child B 3 2 2 2 9.1, childDepth: 6, branch: else
     depth: 6, title: Fin Child B 3 2 2 2 10.1, childDepth: 6, branch: else
     depth: 6, title: Fin Child B 3 2 2 2 11.1, childDepth: 6, branch: else
      depth: 7, title: Fin Child B 3 2 2 2 11 2.1, childDepth: 7, branch: else
      depth: 7, title: Fin Child B 3 2 2 2 11 3.1, childDepth: 7, branch: else
      depth: 7, title: Fin Child B 3 2 2 2 11 4.1, childDepth: 7, branch: else
      depth: 7, title: Fin Child B 3 2 2 2 11 5.1, childDepth: 7, branch: else
      depth: 7, title: Fin Child B 3 2 2 2 11 6.1, childDepth: 7, branch: else
  depth: 3, title: Fin B C3 2.1, childDepth: 3, branch: else
depth: 1, title: Tree-Finish.1, childDepth: , branch: depth==1
depth: 1, title: Home.0, childDepth: , branch: depth==1
 depth: 2, title: About.0, childDepth: 2, branch: depth==2
 depth: 2, title: Projects.0, childDepth: 2, branch: depth==2
  depth: 3, title: Project-1.0, childDepth: 3, branch: else
  depth: 3, title: Project-2.0, childDepth: 3, branch: else
   depth: 4, title: Project 2 Nested.0, childDepth: 4, branch: else
    depth: 5, title: Project 2 Nested 2.0, childDepth: 5, branch: else
 depth: 2, title: Contact.0, childDepth: 2, branch: depth==2
depth: 1, title: Root-2.0, childDepth: , branch: depth==1
depth: 1, title: Root-3.0, childDepth: , branch: depth==1
depth: 1, title: Root-4.0, childDepth: , branch: depth==1
depth: 1, title: Fin Child 1.0, childDepth: , branch: depth==1
 depth: 2, title: Fin Child 2.0, childDepth: 2, branch: depth==2
 depth: 2, title: Fin Child 3.0, childDepth: 2, branch: depth==2
depth: 1, title: Fin Child B 1.0, childDepth: , branch: depth==1
 depth: 2, title: Fin Child B 2.0, childDepth: 2, branch: depth==2
 depth: 2, title: Fin Child B 3.0, childDepth: 2, branch: depth==2
  depth: 3, title: Fin Child B 3 2.0, childDepth: 3, branch: else
   depth: 4, title: Fin Child B 3 2 2.0, childDepth: 4, branch: else
    depth: 5, title: Fin Child B 3 2 2 2.0, childDepth: 5, branch: else
     depth: 6, title: Fin Child B 3 2 2 2 2.0, childDepth: 6, branch: else
     depth: 6, title: Fin Child B 3 2 2 2 3.0, childDepth: 6, branch: else
     depth: 6, title: Fin Child B 3 2 2 2 4.0, childDepth: 6, branch: else
     depth: 6, title: Fin Child B 3 2 2 2 5.0, childDepth: 6, branch: else
     depth: 6, title: Fin Child B 3 2 2 2 6.0, childDepth: 6, branch: else
     depth: 6, title: Fin Child B 3 2 2 2 7.0, childDepth: 6, branch: else
     depth: 6, title: Fin Child B 3 2 2 2 8.0, childDepth: 6, branch: else
     depth: 6, title: Fin Child B 3 2 2 2 9.0, childDepth: 6, branch: else
     depth: 6, title: Fin Child B 3 2 2 2 10.0, childDepth: 6, branch: else
     depth: 6, title: Fin Child B 3 2 2 2 11.0, childDepth: 6, branch: else
      depth: 7, title: Fin Child B 3 2 2 2 11 2.0, childDepth: 7, branch: else
      depth: 7, title: Fin Child B 3 2 2 2 11 3.0, childDepth: 7, branch: else
      depth: 7, title: Fin Child B 3 2 2 2 11 4.0, childDepth: 7, branch: else
      depth: 7, title: Fin Child B 3 2 2 2 11 5.0, childDepth: 7, branch: else
      depth: 7, title: Fin Child B 3 2 2 2 11 6.0, childDepth: 7, branch: else
  depth: 3, title: Fin B C3 2.0, childDepth: 3, branch: else
depth: 1, title: Tree-Finish.0, childDepth: , branch: depth==1
depth: 1, title: Home.1, childDepth: , branch: depth==1
 depth: 2, title: About.1, childDepth: 2, branch: depth==2
 depth: 2, title: Projects.1, childDepth: 2, branch: depth==2
  depth: 3, title: Project-1.1, childDepth: 3, branch: else
  depth: 3, title: Project-2.1, childDepth: 3, branch: else
   depth: 4, title: Project 2 Nested.1, childDepth: 4, branch: else
    depth: 5, title: Project 2 Nested 2.1, childDepth: 5, branch: else
 depth: 2, title: Contact.1, childDepth: 2, branch: depth==2
depth: 1, title: Root-2.1, childDepth: , branch: depth==1
depth: 1, title: Root-3.1, childDepth: , branch: depth==1
depth: 1, title: Root-4.1, childDepth: , branch: depth==1
depth: 1, title: Fin Child 1.1, childDepth: , branch: depth==1
 depth: 2, title: Fin Child 2.1, childDepth: 2, branch: depth==2
 depth: 2, title: Fin Child 3.1, childDepth: 2, branch: depth==2
depth: 1, title: Fin Child B 1.1, childDepth: , branch: depth==1
 depth: 2, title: Fin Child B 2.1, childDepth: 2, branch: depth==2
 depth: 2, title: Fin Child B 3.1, childDepth: 2, branch: depth==2
  depth: 3, title: Fin Child B 3 2.1, childDepth: 3, branch: else
   depth: 4, title: Fin Child B 3 2 2.1, childDepth: 4, branch: else
    depth: 5, title: Fin Child B 3 2 2 2.1, childDepth: 5, branch: else
     depth: 6, title: Fin Child B 3 2 2 2 2.1, childDepth: 6, branch: else
     depth: 6, title: Fin Child B 3 2 2 2 3.1, childDepth: 6, branch: else
     depth: 6, title: Fin Child B 3 2 2 2 4.1, childDepth: 6, branch: else
     depth: 6, title: Fin Child B 3 2 2 2 5.1, childDepth: 6, branch: else
     depth: 6, title: Fin Child B 3 2 2 2 6.1, childDepth: 6, branch: else
     depth: 6, title: Fin Child B 3 2 2 2 7.1, childDepth: 6, branch: else
     depth: 6, title: Fin Child B 3 2 2 2 8.1, childDepth: 6, branch: else
     depth: 6, title: Fin Child B 3 2 2 2 9.1, childDepth: 6, branch: else
     depth: 6, title: Fin Child B 3 2 2 2 10.1, childDepth: 6, branch: else
     depth: 6, title: Fin Child B 3 2 2 2 11.1, childDepth: 6, branch: else
      depth: 7, title: Fin Child B 3 2 2 2 11 2.1, childDepth: 7, branch: else
      depth: 7, title: Fin Child B 3 2 2 2 11 3.1, childDepth: 7, branch: else
      depth: 7, title: Fin Child B 3 2 2 2 11 4.1, childDepth: 7, branch: else
      depth: 7, title: Fin Child B 3 2 2 2 11 5.1, childDepth: 7, branch: else
      depth: 7, title: Fin Child B 3 2 2 2 11 6.1, childDepth: 7, branch: else
  depth: 3, title: Fin B C3 2.1, childDepth: 3, branch: else
depth: 1, title: Tree-Finish.1, childDepth: , branch: depth==1
EOT;

        $this->assertSame($expected, $result);
    }

    public function test_recursive_nodes_on_structures_inside_partials()
    {
        $this->makeNavTree();

        $this->withFakeViews();

        $navTemplate = <<<'EOT'
{{ nav:main include_home="true" }}
<div>{{ depth }} {{ title }}</div>
{{ if children }}{{ *recursive children* }}{{ /if }}
{{ /nav:main }}
EOT;

        $this->viewShouldReturnRaw('nav', $navTemplate);

        $mainTemplate = <<<'EOT'
{{ partial:nav }}
{{ partial:nav }}
--------------------------------------------------------------------------------
{{ nav:main include_home="true" }}
<div>{{ depth }} {{ title }}</div>
{{ if children }}{{ *recursive children* }}{{ /if }}
{{ /nav:main }}
--------------------------------------------------------------------------------
{{ nav:main include_home="true" }}
<div>{{ depth }} {{ title }}</div>
{{ if children }}{{ *recursive children* }}{{ /if }}
{{ /nav:main }}
--------------------------------------------------------------------------------
{{ partial:nav }}
{{ partial:nav }}
EOT;

        $expected = <<<'EOT'
<div>1 Home</div>


<div>1 About</div>

<div>2 Team</div>


<div>2 Leadership</div>



<div>1 Projects</div>

<div>2 Project-1</div>


<div>2 Project-2</div>

<div>3 Project 2 Nested</div>




<div>1 Contact</div>



<div>1 Home</div>


<div>1 About</div>

<div>2 Team</div>


<div>2 Leadership</div>



<div>1 Projects</div>

<div>2 Project-1</div>


<div>2 Project-2</div>

<div>3 Project 2 Nested</div>




<div>1 Contact</div>


--------------------------------------------------------------------------------

<div>1 Home</div>


<div>1 About</div>

<div>2 Team</div>


<div>2 Leadership</div>



<div>1 Projects</div>

<div>2 Project-1</div>


<div>2 Project-2</div>

<div>3 Project 2 Nested</div>




<div>1 Contact</div>


--------------------------------------------------------------------------------

<div>1 Home</div>


<div>1 About</div>

<div>2 Team</div>


<div>2 Leadership</div>



<div>1 Projects</div>

<div>2 Project-1</div>


<div>2 Project-2</div>

<div>3 Project 2 Nested</div>




<div>1 Contact</div>


--------------------------------------------------------------------------------

<div>1 Home</div>


<div>1 About</div>

<div>2 Team</div>


<div>2 Leadership</div>



<div>1 Projects</div>

<div>2 Project-1</div>


<div>2 Project-2</div>

<div>3 Project 2 Nested</div>




<div>1 Contact</div>



<div>1 Home</div>


<div>1 About</div>

<div>2 Team</div>


<div>2 Leadership</div>



<div>1 Projects</div>

<div>2 Project-1</div>


<div>2 Project-2</div>

<div>3 Project 2 Nested</div>




<div>1 Contact</div>
EOT;

        $this->assertSame($expected, trim($this->renderString($mainTemplate, [], true)));
    }

    public function test_recursive_nodes_on_structures()
    {
        $this->makeNavTree();

        $template = <<<'EOT'
{{ nav:main include_home="true" }}
<div>{{ depth }} {{ title }}</div>
{{ if children }}{{ *recursive children* }}{{ /if }}
{{ /nav:main }}
EOT;

        $expected = <<<'EOT'
<div>1 Home</div>


<div>1 About</div>

<div>2 Team</div>


<div>2 Leadership</div>



<div>1 Projects</div>

<div>2 Project-1</div>


<div>2 Project-2</div>

<div>3 Project 2 Nested</div>




<div>1 Contact</div>
EOT;

        $this->assertSame($expected, trim($this->renderString($template, [], true)));

        $template = <<<'EOT'
{{ nav:main include_home="true" }}
<div>ONE: {{ depth }} {{ title }}</div>
{{ if children }}{{ *recursive children* }}{{ /if }}
{{ /nav:main }}

{{ nav:main include_home="true" }}
<div>TWO: {{ depth }} {{ title }}</div>
{{ if children }}{{ *recursive children* }}{{ /if }}
{{ /nav:main }}
EOT;

        $expected = <<<'EOT'
<div>ONE: 1 Home</div>


<div>ONE: 1 About</div>

<div>ONE: 2 Team</div>


<div>ONE: 2 Leadership</div>



<div>ONE: 1 Projects</div>

<div>ONE: 2 Project-1</div>


<div>ONE: 2 Project-2</div>

<div>ONE: 3 Project 2 Nested</div>




<div>ONE: 1 Contact</div>




<div>TWO: 1 Home</div>


<div>TWO: 1 About</div>

<div>TWO: 2 Team</div>


<div>TWO: 2 Leadership</div>



<div>TWO: 1 Projects</div>

<div>TWO: 2 Project-1</div>


<div>TWO: 2 Project-2</div>

<div>TWO: 3 Project 2 Nested</div>




<div>TWO: 1 Contact</div>
EOT;

        $this->assertSame($expected, trim($this->renderString($template, [], true)));

        $template = <<<'EOT'
<ul>
{{ nav:main }}
{{ if depth === 1 }}
<li class="if-depth-one">
{{ title }} - {{ depth }}<br />
{{ if children }}
<ul class="if-depth-one-children">
{{ *recursive children* }}
</ul>
{{ /if }}

</li>
{{ elseif depth == 2 }}
<li class="else-depth-two">
{{ title }} - {{ depth }}<br />
{{ if children }}
<ul class="if-else-depth-two-children">
{{ *recursive children* }}
</ul>
{{ /if }}
</li>

{{ else }}
<li class="else-other-depths">
{{ title }} -- {{ depth }}
</li>
{{ /if }}
{{ /nav:main }}
</ul>
EOT;

        $expected = <<<'EOT'
<ul>


<li class="if-depth-one">
Home - 1<br />


</li>



<li class="if-depth-one">
About - 1<br />

<ul class="if-depth-one-children">


<li class="else-depth-two">
Team - 2<br />

</li>




<li class="else-depth-two">
Leadership - 2<br />

</li>



</ul>


</li>



<li class="if-depth-one">
Projects - 1<br />

<ul class="if-depth-one-children">


<li class="else-depth-two">
Project-1 - 2<br />

</li>




<li class="else-depth-two">
Project-2 - 2<br />

<ul class="if-else-depth-two-children">


<li class="else-other-depths">
Project 2 Nested -- 3
</li>


</ul>

</li>



</ul>


</li>



<li class="if-depth-one">
Contact - 1<br />


</li>


</ul>
EOT;

        $this->assertSame($expected, trim($this->renderString($template, [], true)));

        $template = <<<'EOT'
<ul class="parent-menu">
{{ nav:main }}
{{ if depth == 1 }}
<li {{ if children }} something{{ endif }}>
<a>
{{ title }}
</a>
{{ if children }}
<ul class="child-menu">
{{ *recursive children* }}
</ul>
{{ endif }}
</li>
{{ else }}
<li>
<a>
{{ title }}
</a>
</li>
{{ endif }}
{{ /nav:main }}
</ul>
EOT;

        $expected = <<<'EOT'
<ul class="parent-menu">


<li >
<a>
Home
</a>

</li>



<li  something>
<a>
About
</a>

<ul class="child-menu">


<li>
<a>
Team
</a>
</li>



<li>
<a>
Leadership
</a>
</li>


</ul>

</li>



<li  something>
<a>
Projects
</a>

<ul class="child-menu">


<li>
<a>
Project-1
</a>
</li>



<li>
<a>
Project-2
</a>
</li>


</ul>

</li>



<li >
<a>
Contact
</a>

</li>


</ul>
EOT;

        $this->assertSame($expected, trim($this->renderString($template, [], true)));
    }

    public function test_recursive_node_can_be_root()
    {
        $this->parseNodes(<<<'EOT'
    $template = <<<'EOT'
{{ records }}
    {{ title }}
    <br />
    {{ children }}
        {{ title }}
        <br />
        {{ *recursive children* }}
    {{ /children }}
{{ /records }}
EOT
        );

        // The parseNodes will throw an exception if it fails to parse correctly.
        // We will just assert true is true to shut up the risky assertions warning.
        $this->assertTrue(true);
    }

    public function test_sub_recursive_nodes()
    {
        $data = [
            'records' => [
                'title' => 'One',
                'children' => [
                    [
                        'title' => 'Two',
                    ],
                    [
                        'title' => 'Three',
                        'children' => [
                            [
                                'title' => 'Four',
                                'foo' => 'Baz',
                                'children' => [
                                    [
                                        'title' => 'Five',
                                        'colors' => [
                                            [
                                                'name' => 'Blue',
                                                'colors' => [
                                                    [
                                                        'name' => 'Green',
                                                        'colors' => [
                                                            [
                                                                'name' => 'Yellow',
                                                            ],
                                                        ],
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
        $template = <<<'EOT'
<ul>
{{ records }}
<li>
{{ title }} - {{ depth }} - {{ children_depth }}

{{ if colors }}
Start Colors
{{ colors }}
Outer Title: {{ title }}
Color Name: {{ name }}
Active Depth: {{ depth }}
Outer Depth: {{ children_depth }}
Color Depth: {{ colors_depth }}

{{ if colors }}
REC-COLOR-START
{{ *subrecursive colors* }}
REC-COLOR-STOP
{{ /if }}
{{ /colors }}

End Colors
{{ /if }}

{{ if children }}
<ul>
{{ *recursive children* }}
</ul>
{{ /if }}
</li>
{{ /records }}
</ul>
EOT;

        $expected = <<<'EOT'
<ul>

<li>
One - 1 - 1




<ul>

<li>
Two - 2 - 2




</li>

<li>
Three - 2 - 2




<ul>

<li>
Four - 3 - 3




<ul>

<li>
Five - 4 - 4


Start Colors

Outer Title: Five
Color Name: Blue
Active Depth: 1
Outer Depth: 4
Color Depth: 1


REC-COLOR-START

Outer Title: Five
Color Name: Green
Active Depth: 2
Outer Depth: 4
Color Depth: 2


REC-COLOR-START

Outer Title: Five
Color Name: Yellow
Active Depth: 3
Outer Depth: 4
Color Depth: 3



REC-COLOR-STOP


REC-COLOR-STOP



End Colors



</li>

</ul>

</li>

</ul>

</li>

</ul>

</li>

</ul>
EOT;

        $this->assertSame(StringUtilities::normalizeLineEndings($expected), StringUtilities::normalizeLineEndings($this->renderString($template, $data)));
    }

    public function test_simple_depth_tree_class()
    {
        $data = [
            'records' => [
                'title' => 'One',
                'children' => [
                    [
                        'title' => 'Two',
                    ],
                    [
                        'title' => 'Three',
                        'children' => [
                            [
                                'title' => 'Four',
                                'foo' => 'Baz',
                                'children' => [
                                    [
                                        'title' => 'Five',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $template = <<<'EOT'
<ul>
{{ records }}
<li>
<span>{{ title }} -- {{ depth }}</span>

{{ if children }}
<ul class="depth-{{ depth ?? 'root' }}">
{{ *recursive children* }}
</ul>
{{ /if }}
</li>
{{ /records }}
</ul>
EOT;
        $expected = <<<'EOT'
<ul>

<li>
<span>One -- 1</span>


<ul class="depth-1">

<li>
<span>Two -- 2</span>


</li>

<li>
<span>Three -- 2</span>


<ul class="depth-2">

<li>
<span>Four -- 3</span>


<ul class="depth-3">

<li>
<span>Five -- 4</span>


</li>

</ul>

</li>

</ul>

</li>

</ul>

</li>

</ul>
EOT;

        $this->assertSame(StringUtilities::normalizeLineEndings($expected), StringUtilities::normalizeLineEndings(
            $this->renderString($template, $data)
        ));
    }

    public function test_recursive_node_that_is_not_from_a_tag()
    {
        $data = [
            'records' => [
                'title' => 'One',
                'children' => [
                    [
                        'title' => 'Two',
                    ],
                    [
                        'title' => 'Three',
                        'children' => [
                            [
                                'title' => 'Four',
                                'foo' => 'Baz',
                                'children' => [
                                    ['title' => 'Five'],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $template = <<<'EOT'
<ul>
{{ records }}
<li>{{ title }} - {{ depth }}
{{ if children }}
<ul>
{{ *recursive children* }}
</ul>
{{ /if }}
</li>
{{ /records }}
</ul>
EOT;
        $results = $this->renderString($template, $data);
        $expected = <<<'EOT'
<ul>

<li>One - 1

<ul>

<li>Two - 2

</li>

<li>Three - 2

<ul>

<li>Four - 3

<ul>

<li>Five - 4

</li>

</ul>

</li>

</ul>

</li>

</ul>

</li>

</ul>
EOT;

        $this->assertSame(StringUtilities::normalizeLineEndings($expected), StringUtilities::normalizeLineEndings($results));
    }

    public function test_multiple_navs_on_same_page_inside_conditions_resolve_the_correct_parent()
    {
        $this->makeNavTree();

        $template = <<<'EOT'
{{ nav handle="main" }}
    {{ if depth == 1 }}
        <div class="{{ if is_current || is_parent }}active{{ /if }}">
            <a href="{{ url }}">First: {{ title }}</a>
            {{ if children }}
                <ul>
                    {{ *recursive children* }}
                </ul>
            {{ /if }}
        </div>
    {{ /if }}
    {{ if depth == 2 }}
        <li><a class="{{ if is_current }}active{{ /if }}" href="{{ url }}">First: {{ title }}</a></li>
    {{ /if }}
{{ /nav }}

<hr />
{{ if true }}
    {{ nav handle="main" }}
        {{ if depth == 1 }}
            <div>
                <a href="{{ url }}">Second: {{ title }}</a>
                {{ if children }}
                    <ul>
                        {{ *recursive children* }}
                    </ul>
                {{ /if }}
            </div>
        {{ /if }}
        {{ if depth == 2 }}
            <li><a class="{{ if is_current }}active{{ /if }}" href="{{ url }}">Second: {{ title }}</a></li>
        {{ /if }}
    {{ /nav }}
{{ /if }}
EOT;

        $result = $this->renderString($template, [], true);
        $this->assertStringContainsString('<a href="/">First: Home</a>', $result);
        $this->assertStringContainsString('<a href="about">First: About</a>', $result);
        $this->assertStringContainsString('<li><a class="" href="team">First: Team</a></li>', $result);
        $this->assertStringContainsString('<li><a class="" href="leadership">First: Leadership</a></li>', $result);
        $this->assertStringContainsString('<a href="projects">First: Projects</a>', $result);
        $this->assertStringContainsString('<li><a class="" href="project-1">First: Project-1</a></li>', $result);
        $this->assertStringContainsString('<li><a class="" href="project-2">First: Project-2</a></li>', $result);

        $this->assertStringContainsString('<a href="/">Second: Home</a>', $result);
        $this->assertStringContainsString('<a href="about">Second: About</a>', $result);
        $this->assertStringContainsString('<li><a class="" href="leadership">Second: Leadership</a></li>', $result);
        $this->assertStringContainsString('<a href="projects">Second: Projects</a>', $result);
        $this->assertStringContainsString('<a href="contact">Second: Contact</a>', $result);
    }

    public function test_arbitrary_arrays_can_be_used_in_recursion()
    {
        $data = [
            'parent_data' => [
                'records' => [
                    [
                        'title' => 'One',
                        'records' => [
                            [
                                'title' => 'Two',
                                'records' => [
                                    [
                                        'title' => 'Three',
                                        'records' => [
                                            [
                                                'title' => 'Four',
                                                'records' => [
                                                    [
                                                        'title' => 'Five',
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $template = <<<'EOT'
{{ parent_data }}
    <ul class="parent">
    {{ records }}
        <li>
            <span>{{ title }} -- {{ depth }}</span>
            {{ if records }}
                <ul class="depth-{{ depth }}">
                    {{*recursive records*}}
                </ul>
            {{ /if }}
        </li>
    {{ /records }}
    </ul>
{{ /parent_data }}
EOT;

        $expected = <<<'EXP'
<ul class="parent">
    
        <li>
            <span>One -- 1</span>
            
                <ul class="depth-1">
                    
        <li>
            <span>Two -- 2</span>
            
                <ul class="depth-2">
                    
        <li>
            <span>Three -- 3</span>
            
                <ul class="depth-3">
                    
        <li>
            <span>Four -- 4</span>
            
                <ul class="depth-4">
                    
        <li>
            <span>Five -- 5</span>
            
        </li>
    
                </ul>
            
        </li>
    
                </ul>
            
        </li>
    
                </ul>
            
        </li>
    
                </ul>
            
        </li>
    
    </ul>
EXP;

        $result = trim($this->renderString($template, $data));

        $this->assertSame($expected, $result);
    }

    public function testRecursiveNavigationWithCustomVariables()
    {
        // In this scenario, the custom variable should be
        // updated and persisted after each iteration
        // of the main nav loop. The value should
        // be the same at all recursive levels.
        $this->makeNavTree();

        $template = <<<'EOT'
{{ _custom_var = 'a' /}}
{{ nav handle="main" }}

DEPTH {{ depth }}
TITLE {{ title }}
VAR {{ _custom_var }}

    {{ if children }}
        CHILDREN
        {{ *recursive children* }}
        END_CHILDREN
    {{ /if }}
{{ _custom_var += "a" }}
====
{{ /nav }}
AFTER: {{ _custom_var}}
EOT;

        $expected = <<<'EXPECTED'
DEPTH 1
TITLE Home
VAR a

    

====


DEPTH 1
TITLE About
VAR aa

    
        CHILDREN
        

DEPTH 2
TITLE Team
VAR aa

    

====


DEPTH 2
TITLE Leadership
VAR aa

    

====

        END_CHILDREN
    

====


DEPTH 1
TITLE Projects
VAR aaa

    
        CHILDREN
        

DEPTH 2
TITLE Project-1
VAR aaa

    

====


DEPTH 2
TITLE Project-2
VAR aaa

    
        CHILDREN
        

DEPTH 3
TITLE Project 2 Nested
VAR aaaa

    

====

        END_CHILDREN
    

====

        END_CHILDREN
    

====


DEPTH 1
TITLE Contact
VAR aaaa

    

====

AFTER: aaaaa
EXPECTED;

        $this->assertSame(
            StringUtilities::normalizeLineEndings($expected),
            StringUtilities::normalizeLineEndings(trim($this->renderString($template, [], true)))
        );
    }

    public function testRecursiveNavigationWithCustomVariablesBeforeChildLoop()
    {
        // In this scenario, the custom variable is updated before
        // the child loop, and will be different at each level
        // of recursion. The value will be the same for the
        // last child of the current recursive level and
        // the beginning of the next recursive loop.
        $this->makeNavTree();

        $template = <<<'EOT'
{{ _custom_var = 'a' /}}
{{ nav handle="main" }}

DEPTH {{ depth }}
TITLE {{ title }}
VAR {{ _custom_var }}

    {{ if children }}
        CHILDREN
        {{ _custom_var += "a" }}
        {{ *recursive children* }}
        END_CHILDREN
    {{ /if }}
====
{{ /nav }}
AFTER: {{ _custom_var}}
EOT;

        $expected = <<<'EXPECTED'
DEPTH 1
TITLE Home
VAR a

    
====


DEPTH 1
TITLE About
VAR a

    
        CHILDREN
        
        

DEPTH 2
TITLE Team
VAR aa

    
====


DEPTH 2
TITLE Leadership
VAR aa

    
====

        END_CHILDREN
    
====


DEPTH 1
TITLE Projects
VAR aa

    
        CHILDREN
        
        

DEPTH 2
TITLE Project-1
VAR aaa

    
====


DEPTH 2
TITLE Project-2
VAR aaa

    
        CHILDREN
        
        

DEPTH 3
TITLE Project 2 Nested
VAR aaaa

    
====

        END_CHILDREN
    
====

        END_CHILDREN
    
====


DEPTH 1
TITLE Contact
VAR aaaa

    
====

AFTER: aaaa
EXPECTED;

        $this->assertSame(
            StringUtilities::normalizeLineEndings($expected),
            StringUtilities::normalizeLineEndings(trim($this->renderString($template, [], true)))
        );
    }
}
