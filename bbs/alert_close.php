<?php
include_once('./_common.php');
include_once(G5_PATH.'/head.sub.php');

$msg = isset($msg) ? strip_tags($msg) : '';
$toast_type = $error ? 'error' : 'success';
?>

<script>
if (typeof mgToast === 'function') {
    mgToast("<?php echo addslashes($msg); ?>", "<?php echo $toast_type; ?>", 2000);
}
setTimeout(function() {
    try { window.close(); } catch(e) {}
    setTimeout(function() {
        if (window.history.length) window.history.back();
    }, 500);
}, 1500);
</script>

<?php
include_once(G5_PATH.'/tail.sub.php');
