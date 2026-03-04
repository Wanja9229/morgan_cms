<?php
if (!defined('_GNUBOARD_')) exit;

add_stylesheet('<link rel="stylesheet" href="'.$content_skin_url.'/style.css">', 0);
?>

<article id="ctt" class="ctt_<?php echo $co_id; ?> mg-inner px-4 py-8">
    <div class="bg-mg-bg-secondary rounded-xl border border-mg-bg-tertiary overflow-hidden">
        <div class="px-5 py-4 border-b border-mg-bg-tertiary">
            <h1 class="text-xl font-bold text-mg-text-primary"><?php echo $g5['title']; ?></h1>
        </div>
        <div id="ctt_con" class="prose-morgan px-5 py-6 md:px-6">
            <?php echo $str; ?>
        </div>
    </div>
</article>
