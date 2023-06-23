<?php

return [
    'any' => [
        'config' => [
            'antlers' => 'Bu alanın içeriğinde Antlers ile ayrıştırmayı etkinleştirin.',
            'cast_booleans' => 'true ve false değerlerine sahip seçenekler boolean olarak kaydedilecektir.',
        ],
    ],
    'array' => [
        'config' => [
            'keys' => 'Dizi anahtarlarını (değişkenler) ve isteğe bağlı etiketleri ayarlayın.',
            'mode' => 'Dinamik mod, kullanıcıya verilerin kontrolünü sağlarken, anahtarlı mod vermez.',
        ],
        'title' => 'Dizi',
    ],
    'assets' => [
        'config' => [
            'allow_uploads' => 'Yeni dosya yüklemelerine izin ver.',
            'container' => 'Bu alan için hangi varlık kapsayıcısının kullanılacağını seçin.',
            'folder' => 'Gezinmeye başlamak için klasör.',
            'max_files' => 'Maksimum seçilebilir varlık sayısı.',
            'mode' => 'Tercih ettiğiniz düzen stilini seçin.',
            'restrict' => 'Kullanıcıların diğer klasörlere gitmesini engelleyin.',
            'show_filename' => 'Önizleme görüntüsünün yanında dosya adını gösterin.',
        ],
        'title' => 'Varlıklar',
    ],
    'bard' => [
        'config' => [
            'allow_source' => 'Yazarken HTML kaynak kodunu görüntülemeyi etkinleştirin.',
            'always_show_set_button' => 'Her zaman "Set Ekle" düğmesini göstermek için etkinleştirin.',
            'buttons' => 'Araç çubuğunda hangi düğmelerin gösterileceğini seçin.',
            'container' => 'Bu alan için hangi varlık kapsayıcısının kullanılacağını seçin.',
            'enable_input_rules' => 'İçerik yazarken Markdown tarzı kısayolları etkinleştirir.',
            'enable_paste_rules' => 'İçeriği yapıştırırken Markdown tarzı kısayolları etkinleştirir.',
            'fullscreen' => 'Tam ekran moduna geçmek için etkinleştirin',
            'link_collections' => 'Bu koleksiyonlardan girişler bağlantı seçicide mevcut olacaktır. Bunu boş bırakmak tüm girişleri kullanılabilir hale getirecektir.',
            'link_noopener' => 'Tüm bağlantılarda `rel="noopener"` ayarlayın.',
            'link_noreferrer' => 'Tüm bağlantılarda `rel="noreferrer"` ayarlayın.',
            'previews' => 'Kümeler daraltıldığında gösterilir.',
            'reading_time' => 'Alanın altında tahmini okuma süresini gösterin.',
            'remove_empty_nodes' => 'Boş düğümlerle nasıl başa çıkacağınızı seçin.',
            'save_html' => 'Yapılandırılmış veriler yerine HTML\'yi kaydedin. Bu, şablon işaretlemenizin kontrolünü basitleştirir ancak sınırlandırır.',
            'sets' => 'Kümeler, Bard içeriğinizin herhangi bir yerine eklenebilen yapılandırılabilir alan bloklarıdır.',
            'target_blank' => 'Tüm bağlantılarda `target="_blank"` ayarlayın.',
            'toolbar_mode' => 'Tercih ettiğiniz araç çubuğu stilini seçin.',
        ],
        'title' => 'Bard',
    ],
    'button_group' => [
        'title' => 'Buton Grubu',
    ],
    'checkboxes' => [
        'config' => [
            'inline' => 'Onay kutularını arka arkaya göster.',
            'options' => 'Dizi tuşlarını ve isteğe bağlı etiketlerini ayarlayın.',
        ],
        'title' => 'Onay kutuları',
    ],
    'code' => [
        'config' => [
            'indent_size' => 'Tercih ettiğiniz girinti boyutunu ayarlayın (boşluklarda).',
            'indent_type' => 'Tercih ettiğiniz girinti türünü ayarlayın.',
            'key_map' => 'Tercih edilen klavye kısayolları kümesini seçin.',
            'mode' => 'Sözdizimi vurgulaması için dili seçin.',
            'mode_selectable' => 'Modun kullanıcı tarafından değiştirilip değiştirilemeyeceği.',
            'theme' => 'Tercih ettiğiniz temayı seçin.',
        ],
        'title' => 'Kod',
    ],
    'collections' => [
        'title' => 'Koleksiyon',
    ],
    'color' => [
        'config' => [
            'color_modes' => 'Aralarından seçim yapmak istediğiniz renk modlarını seçin.',
            'default_color_mode' => 'Önceden seçilmiş renk modunu ayarlayın.',
            'lock_opacity' => 'Opaklığın ayarlanmasını önleyerek alfa kaydırıcısını devre dışı bırakır.',
            'swatches' => 'Listeden seçilebilecek renkleri önceden tanımlayın.',
            'theme' => 'Klasik ve mini (daha basit) renk seçici arasında seçim yapın.',
        ],
        'title' => 'Renk',
    ],
    'date' => [
        'config' => [
            'columns' => 'Birden çok ay\'ı aynı anda satırlar ve sütunlar halinde göster',
            'earliest_date' => 'En erken seçilebilir tarihi ayarlayın.',
            'format' => '[PHP tarih formatı](https://www.php.net/manual/en/datetime.format.php) kullanılarak tarihin nasıl saklanması gerektiği.',
            'full_width' => 'Tam genişliği kullanmak için takvimi uzatın.',
            'inline' => 'Açılır giriş alanını atlayın ve takvimi doğrudan gösterin.',
            'latest_date' => 'En son seçilebilir tarihi ayarlayın.',
            'mode' => 'Tek veya aralık modu arasında seçim yapın (aralık, zaman seçiciyi devre dışı bırakır).',
            'rows' => 'Birden çok ayı aynı anda satırlar ve sütunlar halinde göster',
            'time_enabled' => 'Zaman seçiciyi etkinleştirin.',
            'time_seconds_enabled' => 'Zaman seçicide saniyeleri göster.',
        ],
        'title' => 'Tarih',
    ],
    'entries' => [
        'config' => [
            'create' => 'Yeni girdilerin oluşturulmasına izin verin.',
        ],
        'title' => 'Girdiler',
    ],
    'float' => [
        'title' => 'Float',
    ],
    'form' => [
        'config' => [
            'max_items' => 'Maksimum seçilebilir form sayısı.',
        ],
        'title' => 'Form',
    ],
    'grid' => [
        'config' => [
            'add_row' => '"Satır Ekle" düğmesinin etiketini ayarlayın.',
            'fields' => 'Her alan, ızgara tablosunda bir sütun haline gelir.',
            'max_rows' => 'Maksimum sayıda oluşturulabilir satır belirleyin.',
            'min_rows' => 'Minimum sayıda oluşturulabilir satır belirleyin.',
            'mode' => 'Tercih ettiğiniz düzen stilini seçin.',
            'reorderable' => 'Satırın yeniden sıralanmasına izin vermek için etkinleştirin.',
        ],
        'title' => 'Izgara',
    ],
    'hidden' => [
        'title' => 'Gizli',
    ],
    'html' => [
        'title' => 'HTML',
    ],
    'integer' => [
        'title' => 'Integer (Tamsayı)',
    ],
    'link' => [
        'config' => [
            'collections' => 'Bu koleksiyonlardan girişler mevcut olacak. Bunu boş bırakmak, yönlendirilebilir koleksiyonlardan girişleri kullanılabilir hale getirecektir.',
            'container' => 'Bu alan için hangi varlık kapsayıcısının kullanılacağını seçin.',
        ],
        'title' => 'Bağlantı',
    ],
    'list' => [
        'title' => 'Liste',
    ],
    'markdown' => [
        'config' => [
            'automatic_line_breaks' => 'Otomatik satır sonlarını etkinleştirir.',
            'automatic_links' => 'Herhangi bir URL\'nin otomatik olarak bağlanmasını sağlar.',
            'container' => 'Bu alan için hangi varlık kapsayıcısının kullanılacağını seçin.',
            'escape_markup' => 'Satır içi HTML işaretlemesinden çıkar (ör. `<div>` - `&lt;div&gt;`).',
            'folder' => 'Gezinmeye başlamak için klasör.',
            'parser' => 'Özelleştirilmiş bir Markdown ayrıştırıcısının adı. Varsayılan olarak boş bırakın.',
            'restrict' => 'Kullanıcıların diğer klasörlere gitmesini engelleyin.',
            'smartypants' => 'Düz tırnakları otomatik olarak kıvrımlı tırnaklara, tireleri en/em-tirelere ve diğer benzer metin dönüşümlerine dönüştürün.',
        ],
        'title' => 'Markdown',
    ],
    'picker' => [
        'category' => [
            'controls' => [
                'description' => 'Mantığı kontrol edebilen seçilebilir seçenekler veya düğmeler sağlayan alanlar.',
            ],
            'media' => [
                'description' => 'Görüntüleri, videoları veya diğer ortamları depolayan alanlar.',
            ],
            'number' => [
                'description' => 'Numaraları saklayan alanlar.',
            ],
            'relationship' => [
                'description' => 'Diğer kaynaklarla ilişkileri depolayan alanlar.',
            ],
            'special' => [
                'description' => 'Bu alanlar, her biri kendi yolunda özeldir.',
            ],
            'structured' => [
                'description' => 'Yapılandırılmış verileri depolayan alanlar. Hatta bazıları diğer alanları kendi içlerine yerleştirebilir.',
            ],
            'text' => [
                'description' => 'Metin dizelerini, zengin içeriği veya her ikisini depolayan alanlar.',
            ],
        ],
    ],
    'radio' => [
        'config' => [
            'inline' => 'Radyo düğmelerini arka arkaya göster.',
            'options' => 'Dizi tuşlarını ve isteğe bağlı etiketlerini ayarlayın.',
        ],
        'title' => 'Radio',
    ],
    'range' => [
        'config' => [
            'append' => 'Kaydırıcının sonuna (sağ tarafına) metin ekleyin.',
            'max' => 'Maksimum, en sağdaki değer.',
            'min' => 'Minimum, en soldaki değer.',
            'prepend' => 'Kaydırıcının başına (sol tarafa) metin ekleyin.',
            'step' => 'Değerler arasındaki minimum boyut.',
        ],
        'title' => 'Menzil',
    ],
    'relationship' => [
        'config' => [
            'mode' => 'Tercih ettiğiniz UI stilini seçin.',
        ],
    ],
    'replicator' => [
        'config' => [
            'collapse' => [
                'accordion' => 'Bir seferde yalnızca bir kümenin genişletilmesine izin verin',
                'disabled' => 'Tüm setler varsayılan olarak genişletildi',
                'enabled' => 'Tüm setler varsayılan olarak daraltıldı',
            ],
            'max_sets' => 'Maksimum set sayısı.',
            'previews' => 'Kümeler daraltıldığında gösterilir.',
        ],
        'title' => 'Çoğaltıcı',
    ],
    'revealer' => [
        'config' => [
            'input_label' => 'Düğmede veya geçişin yanında gösterilecek bir etiket ayarlayın.',
            'mode' => 'Tercih ettiğiniz düzen stilini seçin.',
        ],
        'title' => 'Ortaya çıkarıcı',
    ],
    'section' => [
        'title' => 'Bölüm',
    ],
    'select' => [
        'config' => [
            'clearable' => 'Seçeneğinizin seçimini kaldırmaya izin vermek için etkinleştirin.',
            'multiple' => 'Çoklu seçime izin ver.',
            'options' => 'Anahtarları ve isteğe bağlı etiketlerini ayarlayın.',
            'placeholder' => 'Yer tutucu metni ayarlayın.',
            'push_tags' => 'Seçenekler listesine yeni oluşturulan etiketleri ekleyin.',
            'searchable' => 'Olası seçenekler arasında aramayı etkinleştirin.',
            'taggable' => 'Önceden tanımlanmış seçeneklere ek olarak yeni seçenekler eklemeye izin ver',
        ],
        'title' => 'Seçiniz',
    ],
    'sites' => [
        'title' => 'Siteler',
    ],
    'slug' => [
        'title' => 'Kalıcı Bağlantı',
    ],
    'structures' => [
        'title' => 'Yapılar',
    ],
    'table' => [
        'title' => 'Tablo',
    ],
    'taggable' => [
        'title' => 'Etiketlenebilir',
    ],
    'taxonomies' => [
        'title' => 'Taksonomiler',
    ],
    'template' => [
        'config' => [
            'blueprint' => 'Bir "plana eşle" seçeneği ekler. [Belgelerde](https://statamic.dev/views#inferring-templates-from-entry-blueprints) daha fazla bilgi edinin.',
            'folder' => 'Yalnızca bu klasördeki şablonları göster.',
            'hide_partials' => 'Kısmi bölümlerin nadiren şablon olarak kullanılması amaçlanmıştır.',
        ],
        'title' => 'Şablon',
    ],
    'terms' => [
        'config' => [
            'create' => 'Yeni terimlerin oluşturulmasına izin verin.',
        ],
        'title' => 'Taksonomi Terimleri',
    ],
    'text' => [
        'config' => [
            'append' => 'Metin girişinin arkasına (sağına) metin ekleyin.',
            'character_limit' => 'Maksimum girilebilir karakter sayısını ayarlayın.',
            'input_type' => 'HTML5 giriş türünü ayarlayın.',
            'placeholder' => 'Yer tutucu metni ayarlayın.',
            'prepend' => 'Metin girişinin önüne (soluna) metin ekleyin.',
        ],
        'title' => 'Metin',
    ],
    'textarea' => [
        'title' => 'Metin Alanı',
    ],
    'time' => [
        'config' => [
            'seconds_enabled' => 'Zaman seçicide saniyeleri göster.',
        ],
        'title' => 'Zaman',
    ],
    'toggle' => [
        'config' => [
            'inline_label' => 'Geçiş girişinin yanında gösterilecek bir satır içi etiket ayarlayın.',
        ],
        'title' => 'Aç/Kapat',
    ],
    'user_groups' => [
        'title' => 'Kullanıcı Grupları',
    ],
    'user_roles' => [
        'title' => 'Kullarıcı Rolleri',
    ],
    'users' => [
        'title' => 'Kullanıcılar',
    ],
    'video' => [
        'title' => 'Video',
    ],
    'yaml' => [
        'title' => 'YAML',
    ],
];
