<?php

return [
    'any' => [
        'config' => [
            'antlers' => 'Habilite a análise de Antlers no conteúdo deste campo.',
            'cast_booleans' => 'As opções com valores verdadeiro e falso serão salvas como booleanos.',
        ],
    ],
    'array' => [
        'config' => [
            'keys' => 'Defina as chaves de array (variáveis) e rótulos opcionais.',
            'mode' => 'O modo dinâmico dá ao usuário o controle dos dados, enquanto o modo com chave não.',
        ],
        'title' => 'Array',
    ],
    'assets' => [
        'config' => [
            'allow_uploads' => 'Permitir uploads de novos arquivos.',
            'container' => 'Escolha qual contêiner de arquivos usar para este campo.',
            'folder' => 'A pasta para começar a navegação.',
            'max_files' => 'O número máximo de arquivos selecionáveis.',
            'mode' => 'Escolha seu estilo de layout preferido.',
            'restrict' => 'Impedir que os usuários naveguem para outras pastas.',
            'show_filename' => 'Mostre o nome do arquivo ao lado da visualização da imagem.',
        ],
        'title' => 'Arquivos',
    ],
    'bard' => [
        'config' => [
            'allow_source' => 'Ative para visualizar o código-fonte HTML durante a gravação.',
            'always_show_set_button' => 'Ative para mostrar sempre o botão "Adicionar Conjunto".',
            'buttons' => 'Escolha quais botões mostrar na barra de ferramentas.',
            'container' => 'Escolha qual contêiner de arquivos usar para este campo.',
            'enable_input_rules' => 'Ativa atalhos no estilo Markdown ao digitar conteúdo.',
            'enable_paste_rules' => 'Ativa atalhos no estilo Markdown ao colar conteúdo.',
            'fullscreen' => 'Ative para alternar para o modo de tela cheia',
            'link_collections' => 'As entradas dessas coleções estarão disponíveis no seletor de links. Deixar isso em branco fará com que todas as entradas fiquem disponíveis.',
            'link_noopener' => 'Defina `rel="noopener"` em todos os links.',
            'link_noreferrer' => 'Defina `rel="noreferrer"` em todos os links.',
            'previews' => 'Mostrado quando os conjuntos são recolhidos.',
            'reading_time' => 'Mostra o tempo estimado de leitura na parte inferior do campo.',
            'remove_empty_nodes' => 'Escolha como lidar com nós vazios.',
            'save_html' => 'Salve HTML em vez de dados estruturados. Isso simplifica, mas limita o controle da marcação do seu modelo.',
            'sets' => 'Conjuntos são blocos de campos configuráveis que podem ser inseridos em qualquer lugar do seu conteúdo Bard.',
            'target_blank' => 'Defina `target="_blank"` em todos os links.',
            'toolbar_mode' => 'Escolha qual estilo de barra de ferramentas você prefere.',
        ],
        'title' => 'Bard',
    ],
    'button_group' => [
        'title' => 'Grupo de Botões',
    ],
    'checkboxes' => [
        'config' => [
            'inline' => 'Mostre as caixas de seleção em uma linha.',
            'options' => 'Defina as chaves de array e seus rótulos opcionais.',
        ],
        'title' => 'Caixas de Seleção',
    ],
    'code' => [
        'config' => [
            'indent_size' => 'Defina o tamanho de recuo preferido (em espaços).',
            'indent_type' => 'Defina seu tipo preferido de indentação.',
            'key_map' => 'Escolha o conjunto preferido de atalhos de teclado.',
            'mode' => 'Escolha a linguagem para realce de sintaxe.',
            'mode_selectable' => 'Se o modo pode ser alterado pelo usuário.',
            'theme' => 'Escolha o seu tema preferido.',
        ],
        'title' => 'Código',
    ],
    'collections' => [
        'title' => 'Coleções',
    ],
    'color' => [
        'config' => [
            'color_modes' => 'Escolha quais modos de cor você deseja escolher.',
            'default_color_mode' => 'Defina o modo de cor pré-selecionado.',
            'lock_opacity' => 'Desativa o controle deslizante alfa, evitando ajustes na opacidade.',
            'swatches' => 'Pré-defina as cores que podem ser selecionadas em uma lista.',
            'theme' => 'Escolha entre o seletor de cores clássico e mini (simples).',
        ],
        'title' => 'Cor',
    ],
    'date' => [
        'config' => [
            'columns' => 'Mostrar vários meses de uma só vez, em linhas e colunas',
            'earliest_date' => 'Defina a primeira data selecionável.',
            'format' => 'Como a data deve ser armazenada, usando o [formato de data PHP](https://www.php.net/manual/pt_BR/datetime.format.php).',
            'full_width' => 'Estique o calendário para usar toda a largura.',
            'inline' => 'Ignore o campo de entrada suspenso e mostre o calendário diretamente.',
            'latest_date' => 'Defina a última data selecionável.',
            'mode' => 'Escolha entre o modo único ou de intervalo (o intervalo desativa o seletor de tempo).',
            'rows' => 'Mostrar vários meses de uma só vez, em linhas e colunas',
            'time_enabled' => 'Ative o seletor de hora.',
            'time_seconds_enabled' => 'Mostrar segundos no seletor de horas.',
        ],
        'title' => 'Data',
    ],
    'entries' => [
        'config' => [
            'create' => 'Permitir a criação de novas entradas.',
        ],
        'title' => 'Entradas',
    ],
    'float' => [
        'title' => 'Flutuante',
    ],
    'form' => [
        'config' => [
            'max_items' => 'O número máximo de formulários selecionáveis.',
        ],
        'title' => 'Formulário',
    ],
    'grid' => [
        'config' => [
            'add_row' => 'Defina o rótulo do botão "Adicionar Linha".',
            'fields' => 'Cada campo se torna uma coluna na tabela de grade.',
            'max_rows' => 'Defina um número máximo de linhas criáveis.',
            'min_rows' => 'Defina um número mínimo de linhas criáveis.',
            'mode' => 'Escolha seu estilo de layout preferido.',
            'reorderable' => 'Ative para permitir a reordenação de linhas.',
        ],
        'title' => 'Grid',
    ],
    'hidden' => [
        'title' => 'Oculto',
    ],
    'html' => [
        'title' => 'HTML',
    ],
    'integer' => [
        'title' => 'Inteiro',
    ],
    'link' => [
        'config' => [
            'collections' => 'As entradas dessas coleções estarão disponíveis. Deixar isso em branco fará com que as entradas das coleções roteáveis fiquem disponíveis.',
            'container' => 'Escolha qual contêiner de arquivos usar para este campo.',
        ],
        'title' => 'Link',
    ],
    'list' => [
        'title' => 'Lista',
    ],
    'markdown' => [
        'config' => [
            'automatic_line_breaks' => 'Habilita quebras de linha automáticas.',
            'automatic_links' => 'Ativa a vinculação automática de qualquer URL.',
            'container' => 'Escolha qual contêiner de arquivos usar para este campo.',
            'escape_markup' => 'Escape da marcação HTML embutida (por exemplo, `<div>` para `&lt;div&gt;`).',
            'folder' => 'A pasta para começar a navegação.',
            'parser' => 'O nome de um analisador Markdown personalizado. Deixe em branco para o padrão.',
            'restrict' => 'Impedir que os usuários naveguem para outras pastas.',
            'smartypants' => 'Converta automaticamente aspas retas em aspas curvas, traços em traços largos e outras transformações de texto semelhantes.',
        ],
        'title' => 'Markdown',
    ],
    'picker' => [
        'category' => [
            'controls' => [
                'description' => 'Campos que fornecem opções selecionáveis ou botões que podem controlar a lógica.',
            ],
            'media' => [
                'description' => 'Campos que armazenam imagens, vídeos ou outras mídias.',
            ],
            'number' => [
                'description' => 'Campos que armazenam números.',
            ],
            'relationship' => [
                'description' => 'Campos que armazenam relacionamentos com outros recursos.',
            ],
            'special' => [
                'description' => 'Esses campos são especiais, cada um à sua maneira.',
            ],
            'structured' => [
                'description' => 'Campos que armazenam dados estruturados. Alguns podem até aninhar outros campos dentro de si.',
            ],
            'text' => [
                'description' => 'Campos que armazenam sequências de texto, conteúdo avançado ou ambos.',
            ],
        ],
    ],
    'radio' => [
        'config' => [
            'inline' => 'Mostre os botões de opção em uma linha',
            'options' => 'Defina as chaves de array e seus rótulos opcionais.',
        ],
        'title' => 'Botão de Escolha (Radio)',
    ],
    'range' => [
        'config' => [
            'append' => 'Adicione texto ao final (lado direito) do controle deslizante.',
            'max' => 'O valor máximo, mais à direita.',
            'min' => 'O valor mínimo, mais à esquerda.',
            'prepend' => 'Adicione texto ao início (lado esquerdo) do controle deslizante.',
            'step' => 'O tamanho mínimo entre os valores.',
        ],
        'title' => 'Controle deslizante (Range)',
    ],
    'relationship' => [
        'config' => [
            'mode' => 'Escolha seu estilo de interface do usuário preferido.',
        ],
    ],
    'replicator' => [
        'config' => [
            'collapse' => [
                'accordion' => 'Permitir que apenas um conjunto seja expandido por vez',
                'disabled' => 'Todos os conjuntos expandidos por padrão',
                'enabled' => 'Todos os conjuntos recolhidos por padrão',
            ],
            'max_sets' => 'O número máximo de conjuntos.',
            'previews' => 'Mostrado quando os conjuntos são recolhidos.',
        ],
        'title' => 'Replicador',
    ],
    'revealer' => [
        'config' => [
            'mode' => 'Escolha seu estilo de layout preferido.',
        ],
        'title' => 'Revelador',
    ],
    'section' => [
        'title' => 'Seção',
    ],
    'select' => [
        'config' => [
            'clearable' => 'Ative para permitir desmarcar sua opção.',
            'multiple' => 'Permitir seleções múltiplas.',
            'options' => 'Defina as chaves e seus rótulos opcionais.',
            'placeholder' => 'Defina o texto do placeholder.',
            'push_tags' => 'Adicione tags recém-criadas à lista de opções.',
            'searchable' => 'Habilite a busca pelas opções possíveis.',
            'taggable' => 'Permitir adicionar novas opções além das opções pré-definidas',
        ],
        'title' => 'Seletor',
    ],
    'sites' => [
        'title' => 'Sites',
    ],
    'slug' => [
        'title' => 'Slug',
    ],
    'structures' => [
        'title' => 'Estruturas',
    ],
    'table' => [
        'title' => 'Tabela',
    ],
    'taggable' => [
        'title' => 'Marcável',
    ],
    'taxonomies' => [
        'title' => 'Taxonomias',
    ],
    'template' => [
        'config' => [
            'blueprint' => 'Adiciona uma opção "mapear para diagrama". Saiba mais na [documentação](https://statamic.dev/views#inferring-templates-from-entry-blueprints).',
            'folder' => 'Mostrar apenas modelos nesta pasta.',
            'hide_partials' => 'Parciais raramente são usados como modelos.',
        ],
        'title' => 'Modelo',
    ],
    'terms' => [
        'config' => [
            'create' => 'Permitir a criação de novos termos.',
        ],
        'title' => 'Termos de Taxonomia',
    ],
    'text' => [
        'config' => [
            'append' => 'Adicione texto após (à direita) da entrada de texto.',
            'character_limit' => 'Defina o número máximo de caracteres editáveis.',
            'input_type' => 'Defina o tipo de entrada HTML5.',
            'placeholder' => 'Defina o texto de placeholder.',
            'prepend' => 'Adicione texto antes (à esquerda) da entrada de texto.',
        ],
        'title' => 'Texto',
    ],
    'textarea' => [
        'title' => 'Área de Texto',
    ],
    'time' => [
        'config' => [
            'seconds_enabled' => 'Mostrar segundos no seletor de horas.',
        ],
        'title' => 'Tempo',
    ],
    'toggle' => [
        'config' => [
            'inline_label' => 'Defina um rótulo embutido para ser mostrado ao lado da entrada de alternância.',
        ],
        'title' => 'Alternador',
    ],
    'user_groups' => [
        'title' => 'Grupos de Usuários',
    ],
    'user_roles' => [
        'title' => 'Funções de Usuário',
    ],
    'users' => [
        'title' => 'Usuários',
    ],
    'video' => [
        'title' => 'Vídeo',
    ],
    'yaml' => [
        'title' => 'YAML',
    ],
];
