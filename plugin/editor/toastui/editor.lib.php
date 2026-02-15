<?php
if (!defined('_GNUBOARD_')) exit;

function editor_html($id, $content, $is_dhtml_editor=true)
{
    global $g5, $config, $w, $board, $write;
    static $js = true;

    if(
        $is_dhtml_editor && $content &&
        (
        (!$w && (isset($board['bo_insert_content']) && !empty($board['bo_insert_content'])))
        || ($w == 'u' && isset($write['wr_option']) && strpos($write['wr_option'], 'html') === false )
        )
    ){
        if( preg_match('/\r|\n/', $content) && $content === strip_tags($content, '<a><strong><b>') ) {
            $content = nl2br($content);
        }
    }

    $editor_url = G5_EDITOR_URL.'/'.$config['cf_editor'];
    $html = "";

    if ($is_dhtml_editor && $js) {
        // Toast UI Editor CDN (3.2.2)
        $html .= "\n".'<link rel="stylesheet" href="https://uicdn.toast.com/editor/3.2.2/toastui-editor.min.css">';
        $html .= "\n".'<link rel="stylesheet" href="https://uicdn.toast.com/editor/3.2.2/theme/toastui-editor-dark.min.css">';
        $html .= "\n".'<link rel="stylesheet" href="'.$editor_url.'/morgan-dark.css">';
        $html .= "\n".'<script src="https://uicdn.toast.com/editor/3.2.2/toastui-editor-all.min.js"></script>';
        $html .= "\n".'<script>var toastEditors = {}, ed_nonce = "'.ft_nonce_create('toastui').'";</script>';
        $js = false;
    }

    if ($is_dhtml_editor) {
        $upload_base = $editor_url.'/imageUpload/upload.php';

        $html .= "\n".'<div id="toast_'.$id.'" class="toastui-editor-dark-wrap"></div>';
        $html .= "\n".'<textarea id="'.$id.'" name="'.$id.'" style="display:none">'.$content.'</textarea>';
        $html .= "\n<script>";
        $html .= "\n(function() {";
        $html .= "\n  var container = document.getElementById('toast_{$id}');";
        $html .= "\n  var textarea = document.getElementById('{$id}');";
        $html .= "\n  var uploadUrl = '{$upload_base}?_nonce=' + encodeURIComponent(ed_nonce);";
        $html .= "\n  var emotiBtn = document.createElement('button');";
        $html .= "\n  emotiBtn.type = 'button';";
        $html .= "\n  emotiBtn.className = 'toastui-editor-toolbar-icons';";
        $html .= "\n  emotiBtn.setAttribute('aria-label', 'Emoticon');";
        $html .= "\n  emotiBtn.style.cssText = 'background-image:none;display:inline-flex;align-items:center;justify-content:center;';";
        $html .= "\n  emotiBtn.innerHTML = '<svg width=\"16\" height=\"16\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"1.6\" viewBox=\"0 0 24 24\"><circle cx=\"12\" cy=\"12\" r=\"10\"/><path stroke-linecap=\"round\" d=\"M8 14s1.5 2 4 2 4-2 4-2\"/><circle cx=\"9\" cy=\"10\" r=\"1\" fill=\"currentColor\" stroke=\"none\"/><circle cx=\"15\" cy=\"10\" r=\"1\" fill=\"currentColor\" stroke=\"none\"/></svg>';";
        $html .= "\n  emotiBtn.addEventListener('click', function(e) { e.stopPropagation(); if(typeof MgEmoticonPicker!=='undefined') MgEmoticonPicker.toggleInToolbar('{$id}', this); });";
        $html .= "\n  var editor = new toastui.Editor({";
        $html .= "\n    el: container,";
        $html .= "\n    height: '400px',";
        $html .= "\n    initialEditType: 'wysiwyg',";
        $html .= "\n    initialValue: textarea.value,";
        $html .= "\n    previewStyle: 'vertical',";
        $html .= "\n    theme: 'dark',";
        $html .= "\n    usageStatistics: false,";
        $html .= "\n    hideModeSwitch: false,";
        $html .= "\n    toolbarItems: [";
        $html .= "\n      ['heading', 'bold', 'italic', 'strike'],";
        $html .= "\n      ['hr', 'quote'],";
        $html .= "\n      ['ul', 'ol'],";
        $html .= "\n      ['table', 'image', 'link'],";
        $html .= "\n      ['code', 'codeblock', {el: emotiBtn, tooltip: '이모티콘', name: 'emoticon'}]";
        $html .= "\n    ],";
        $html .= "\n    hooks: {";
        $html .= "\n      addImageBlobHook: function(blob, callback) {";
        $html .= "\n        var formData = new FormData();";
        $html .= "\n        formData.append('file', blob);";
        $html .= "\n        var xhr = new XMLHttpRequest();";
        $html .= "\n        xhr.open('POST', uploadUrl);";
        $html .= "\n        xhr.onload = function() {";
        $html .= "\n          if (xhr.status === 200) {";
        $html .= "\n            try {";
        $html .= "\n              var res = JSON.parse(xhr.responseText);";
        $html .= "\n              if (res.url) callback(res.url, blob.name || 'image');";
        $html .= "\n              else alert('이미지 업로드 실패');";
        $html .= "\n            } catch(e) { alert('이미지 업로드 오류'); }";
        $html .= "\n          } else { alert('이미지 업로드 실패 ('+xhr.status+')'); }";
        $html .= "\n        };";
        $html .= "\n        xhr.onerror = function() { alert('이미지 업로드 네트워크 오류'); };";
        $html .= "\n        xhr.send(formData);";
        $html .= "\n      }";
        $html .= "\n    }";
        $html .= "\n  });";
        $html .= "\n  toastEditors['{$id}'] = editor;";
        $html .= "\n})();";
        $html .= "\n</script>";
    } else {
        // DHTML 에디터 미사용 시 styled textarea
        $html .= "\n".'<textarea id="'.$id.'" name="'.$id.'" class="input" style="width:100%;height:300px">'.$content.'</textarea>';
    }

    return $html;
}

function get_editor_js($id, $is_dhtml_editor=true)
{
    if ($is_dhtml_editor) {
        return "if(typeof toastEditors !== 'undefined' && toastEditors['{$id}']) { document.getElementById('{$id}').value = toastEditors['{$id}'].getHTML(); }\n";
    } else {
        return "var {$id}_editor = document.getElementById('{$id}');\n";
    }
}

function chk_editor_js($id, $is_dhtml_editor=true)
{
    if ($is_dhtml_editor) {
        return "if(typeof toastEditors !== 'undefined' && toastEditors['{$id}']) { var _html = toastEditors['{$id}'].getHTML(); if(!_html || (_html.replace(/<[^>]*>/g,'').trim() === '' && _html.indexOf('<img') === -1)) { alert('내용을 입력해 주십시오.'); toastEditors['{$id}'].focus(); return false; } }\n";
    } else {
        return "if (!document.getElementById('{$id}').value) { alert('내용을 입력해 주십시오.'); document.getElementById('{$id}').focus(); return false; }\n";
    }
}

/*
https://github.com/timostamm/NonceUtil-PHP
*/

if (!defined('FT_NONCE_UNIQUE_KEY'))
    define( 'FT_NONCE_UNIQUE_KEY' , sha1($_SERVER['SERVER_SOFTWARE'].G5_MYSQL_USER.session_id().G5_TABLE_PREFIX) );

if (!defined('FT_NONCE_SESSION_KEY'))
    define( 'FT_NONCE_SESSION_KEY' , substr(md5(FT_NONCE_UNIQUE_KEY), 5) );

if (!defined('FT_NONCE_DURATION'))
    define( 'FT_NONCE_DURATION' , 60 * 60 );

if (!defined('FT_NONCE_KEY'))
    define( 'FT_NONCE_KEY' , '_nonce' );

if(!function_exists('ft_nonce_create_query_string')){
    function ft_nonce_create_query_string( $action = '' , $user = '' ){
        return FT_NONCE_KEY."=".ft_nonce_create( $action , $user );
    }
}

if(!function_exists('ft_get_secret_key')){
    function ft_get_secret_key($secret){
        return md5(FT_NONCE_UNIQUE_KEY.$secret);
    }
}

if(!function_exists('ft_nonce_create')){
    function ft_nonce_create( $action = '',$user='', $timeoutSeconds=FT_NONCE_DURATION ){
        $secret = ft_get_secret_key($action.$user);
        set_session('token_'.FT_NONCE_SESSION_KEY, $secret);
        $salt = ft_nonce_generate_hash();
        $time = time();
        $maxTime = $time + $timeoutSeconds;
        $nonce = $salt . "|" . $maxTime . "|" . sha1( $salt . $secret . $maxTime );
        return $nonce;
    }
}

if(!function_exists('ft_nonce_is_valid')){
    function ft_nonce_is_valid( $nonce, $action = '', $user='' ){
        $secret = ft_get_secret_key($action.$user);
        $token = get_session('token_'.FT_NONCE_SESSION_KEY);
        if ($secret != $token){
            return false;
        }
        if (is_string($nonce) == false) {
            return false;
        }
        $a = explode('|', $nonce);
        if (count($a) != 3) {
            return false;
        }
        $salt = $a[0];
        $maxTime = intval($a[1]);
        $hash = $a[2];
        $back = sha1( $salt . $secret . $maxTime );
        if ($back != $hash) {
            return false;
        }
        if (time() > $maxTime) {
            return false;
        }
        return true;
    }
}

if(!function_exists('ft_nonce_generate_hash')){
    function ft_nonce_generate_hash(){
        $length = 10;
        $chars='1234567890qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM';
        $ll = strlen($chars)-1;
        $o = '';
        while (strlen($o) < $length) {
            $o .= $chars[ rand(0, $ll) ];
        }
        return $o;
    }
}
