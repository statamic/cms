<?php

return [

    'super' => '超級管理員',
    'super_desc' => '超級管理員對於控制面板的所有功能有完整的控制與存取權限。請審慎給予此角色。',

    'group_cp' => '控制面板',
    'access_cp' => '可存取控制面板',
    'access_cp_desc' => '允許存取控制面板，但不保證能在控制面板內能做任何操作。',
    'configure_fields' => '設定欄位',
    'configure_fields_desc' => '可編輯藍圖、欄位集、及其欄位。',
    'configure_addons' => '設定附加元件',
    'configure_addons_desc' => '可存取附加元件區域以安裝或解除安裝附加元件。',

    'group_collections' => '條目集',
    'configure_collections' => '設定條目集',
    'configure_collections_desc' => '給予存取所有與條目集相關的權限',
    'view_{collection}_entries' => '檢視 :collection 條目',
    'edit_{collection}_entries' => '編輯條目',
    'create_{collection}_entries' => '建立新條目',
    'delete_{collection}_entries' => '刪除條目',
    'publish_{collection}_entries' => '管理發佈狀態',
    'publish_{collection}_entries_desc' => '可將草稿更改為已發佈狀態等',
    'reorder_{collection}_entries' => '重新排序條目',
    'reorder_{collection}_entries_desc' => '啟用拖放排序',
    'edit_other_authors_{collection}_entries' => '編輯其他作者的條目',
    'publish_other_authors_{collection}_entries' => '管理其他作者條目的發表狀態',
    'delete_other_authors_{collection}_entries' => '刪除其他作者的條目',

    'group_taxonomies' => '分類',
    'configure_taxonomies' => '設定分類',
    'configure_taxonomies_desc' => '給予存取所有與分類相關的權限',
    'view_{taxonomy}_terms' => '檢視 :taxonomy 字詞組',
    'edit_{taxonomy}_terms' => '編輯字詞組',
    'create_{taxonomy}_terms' => '建立新字詞組',
    'delete_{taxonomy}_terms' => '刪除字詞組',
    'publish_{taxonomy}_terms' => '管理發佈狀態',
    'reorder_{taxonomy}_terms' => '重新排序字詞組',

    'group_navigation' => '導航',
    'configure_navs' => '設定導航',
    'configure_navs_desc' => '給予存取所有導航相關的權限',
    'view_{nav}_nav' => '檢視 :nav 導航',
    'edit_{nav}_nav' => '編輯導航',

    'group_globals' => '全域',
    'configure_globals' => '設定全域',
    'configure_globals_desc' => '給予存取所有全域相關的權限',
    'edit_{global}_globals' => '編輯 :global 全域',

    'group_assets' => '素材',
    'configure_asset_containers' => '設定素材容器',
    'configure_asset_containers_desc' => '給予存取所有素材相關的權限',
    'view_{container}_assets' => '檢視 :container 權限',
    'upload_{container}_assets' => '上傳新素材',
    'edit_{container}_assets' => '編輯素材',
    'move_{container}_assets' => '移動素材',
    'rename_{container}_assets' => '重新命名素材',
    'delete_{container}_assets' => '刪除素材',

    'group_forms' => '表單',
    'configure_forms' => '設定表單',
    'configure_forms_desc' => '給予存取所有表單相關的權限',
    'view_{form}_form_submissions' => '檢視 :form 提交',
    'delete_{form}_form_submissions' => '刪除 :form 提交',

    'group_users' => '使用者',
    'view_users' => '檢視使用者',
    'edit_users' => '編輯使用者',
    'create_users' => '建立使用者',
    'delete_users' => '刪除使用者',
    'change_passwords' => '更改密碼',
    'edit_user_groups' => '編輯群組',
    'edit_roles' => '編輯角色',

    'group_updates' => '更新',
    'view_updates' => '檢視更新',
    'perform_updates' => '執行更新',

    'group_utilities' => '公用程式',
    'access_utility' => ':title',
    'access_utility_desc' => '給予存取所有 :title 公用程式的權限',

    'group_misc' => '其他',
    'resolve_duplicate_ids' => '解析重複的 ID',
    'resolve_duplicate_ids_desc' => '給予檢視與解析重複 ID 的權限。',

];
