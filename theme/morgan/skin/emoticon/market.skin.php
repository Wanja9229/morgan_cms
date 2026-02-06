<?php
if (!defined('_GNUBOARD_')) exit;
?>

<div class="max-w-5xl mx-auto">
    <!-- 헤더 -->
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-bold text-mg-text-primary">이모티콘 상점</h2>
        <?php if ($is_member) { ?>
        <a href="<?php echo G5_BBS_URL; ?>/inventory.php?tab=emoticon" class="btn btn-secondary text-sm">내 이모티콘</a>
        <?php } ?>
    </div>

    <!-- 정렬 탭 -->
    <div class="flex gap-2 mb-4">
        <a href="?sort=latest" class="px-3 py-1.5 rounded text-sm <?php echo $sort === 'latest' ? 'bg-mg-accent text-white' : 'bg-mg-bg-tertiary text-mg-text-secondary hover:text-mg-text-primary'; ?>">최신순</a>
        <a href="?sort=popular" class="px-3 py-1.5 rounded text-sm <?php echo $sort === 'popular' ? 'bg-mg-accent text-white' : 'bg-mg-bg-tertiary text-mg-text-secondary hover:text-mg-text-primary'; ?>">인기순</a>
        <a href="?sort=free" class="px-3 py-1.5 rounded text-sm <?php echo $sort === 'free' ? 'bg-mg-accent text-white' : 'bg-mg-bg-tertiary text-mg-text-secondary hover:text-mg-text-primary'; ?>">무료</a>
    </div>

    <!-- 셋 목록 그리드 -->
    <?php if (!empty($sets['items'])) { ?>
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
        <?php foreach ($sets['items'] as $item) {
            $owned = $is_member ? mg_owns_emoticon_set($member['mb_id'], $item['es_id']) : false;
        ?>
        <div class="card cursor-pointer hover:border-mg-accent/50 transition-colors" onclick="showSetDetail(<?php echo $item['es_id']; ?>)">
            <div class="flex justify-center mb-3">
                <?php if ($item['es_preview']) { ?>
                <img src="<?php echo htmlspecialchars($item['es_preview']); ?>" alt="" class="w-20 h-20 object-contain">
                <?php } else { ?>
                <div class="w-20 h-20 bg-mg-bg-tertiary rounded-lg flex items-center justify-center text-mg-text-muted text-2xl">?</div>
                <?php } ?>
            </div>
            <h3 class="text-sm font-semibold text-mg-text-primary text-center truncate"><?php echo htmlspecialchars($item['es_name']); ?></h3>
            <?php if ($item['es_creator_id']) { ?>
            <p class="text-xs text-mg-text-muted text-center mt-0.5">by <?php echo htmlspecialchars($item['es_creator_id']); ?></p>
            <?php } ?>
            <div class="flex items-center justify-between mt-2">
                <span class="text-xs text-mg-text-muted"><?php echo (int)$item['em_count']; ?>개</span>
                <?php if ($owned) { ?>
                <span class="badge badge-success text-xs">보유중</span>
                <?php } elseif ((int)$item['es_price'] === 0) { ?>
                <span class="text-xs font-bold text-mg-success">무료</span>
                <?php } else { ?>
                <span class="text-xs font-bold text-mg-accent"><?php echo number_format((int)$item['es_price']); ?>P</span>
                <?php } ?>
            </div>
        </div>
        <?php } ?>
    </div>

    <!-- 페이지네이션 -->
    <?php if ($sets['total_page'] > 1) { ?>
    <div class="flex justify-center gap-1 mt-6">
        <?php
        $qs = 'sort=' . urlencode($sort);
        $start_p = max(1, $page - 2);
        $end_p = min($sets['total_page'], $page + 2);
        if ($page > 1) echo '<a href="?'.$qs.'&page='.($page-1).'" class="px-3 py-1.5 rounded bg-mg-bg-tertiary text-mg-text-secondary text-sm">&lsaquo;</a>';
        for ($i = $start_p; $i <= $end_p; $i++) {
            if ($i == $page) echo '<span class="px-3 py-1.5 rounded bg-mg-accent text-white text-sm">'.$i.'</span>';
            else echo '<a href="?'.$qs.'&page='.$i.'" class="px-3 py-1.5 rounded bg-mg-bg-tertiary text-mg-text-secondary text-sm hover:text-mg-text-primary">'.$i.'</a>';
        }
        if ($page < $sets['total_page']) echo '<a href="?'.$qs.'&page='.($page+1).'" class="px-3 py-1.5 rounded bg-mg-bg-tertiary text-mg-text-secondary text-sm">&rsaquo;</a>';
        ?>
    </div>
    <?php } ?>

    <?php } else { ?>
    <div class="card text-center py-12">
        <p class="text-mg-text-muted mb-2">아직 등록된 이모티콘 셋이 없습니다.</p>
    </div>
    <?php } ?>
</div>

<!-- 셋 상세 모달 -->
<div id="emoticonDetailModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/60" onclick="if(event.target===this) closeSetDetail();">
    <div class="bg-mg-bg-secondary border border-mg-bg-tertiary rounded-xl max-w-lg w-11/12 max-h-[80vh] overflow-y-auto p-5">
        <div id="emoticonDetailContent">
            <div class="text-center py-8 text-mg-text-muted">로딩중...</div>
        </div>
    </div>
</div>

<script>
function showSetDetail(esId) {
    var modal = document.getElementById('emoticonDetailModal');
    var content = document.getElementById('emoticonDetailContent');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    content.innerHTML = '<div class="text-center py-8 text-mg-text-muted">로딩중...</div>';

    var meta = document.querySelector('meta[name="mg-bbs-url"]');
    var bbs = meta ? meta.content : '/bbs';

    var xhr = new XMLHttpRequest();
    xhr.open('GET', bbs + '/emoticon_api.php?action=set_detail&es_id=' + esId, true);
    xhr.onload = function() {
        if (xhr.status === 200) {
            var data = JSON.parse(xhr.responseText);
            if (data.error) {
                content.innerHTML = '<p class="text-mg-error text-center py-4">' + data.error + '</p>';
                return;
            }
            renderSetDetail(data, content);
        }
    };
    xhr.send();
}

function renderSetDetail(data, container) {
    var html = '';
    html += '<div class="flex items-center justify-between mb-4">';
    html += '<h3 class="text-lg font-bold text-mg-text-primary">' + escHtml(data.name) + '</h3>';
    html += '<button type="button" onclick="closeSetDetail()" class="text-mg-text-muted hover:text-mg-text-primary text-xl">&times;</button>';
    html += '</div>';

    if (data.desc) {
        html += '<p class="text-sm text-mg-text-secondary mb-4">' + escHtml(data.desc) + '</p>';
    }

    if (data.creator) {
        html += '<p class="text-xs text-mg-text-muted mb-3">제작자: ' + escHtml(data.creator) + ' | 판매: ' + data.sales + '개</p>';
    }

    // 이모티콘 그리드
    html += '<div class="grid grid-cols-6 gap-2 mb-4 bg-mg-bg-primary rounded-lg p-3">';
    data.emoticons.forEach(function(em) {
        html += '<div class="flex items-center justify-center p-1" title="' + escHtml(em.code) + '">';
        html += '<img src="' + escHtml(em.image) + '" alt="' + escHtml(em.code) + '" class="w-10 h-10 object-contain">';
        html += '</div>';
    });
    html += '</div>';

    // 구매 영역
    html += '<div class="flex items-center justify-between pt-3 border-t border-mg-bg-tertiary">';
    if (data.owned) {
        html += '<span class="badge badge-success">보유중</span>';
    } else if (data.price === 0) {
        html += '<span class="text-sm font-bold text-mg-success">무료</span>';
    } else {
        html += '<span class="text-sm font-bold text-mg-accent">' + data.price.toLocaleString() + 'P</span>';
    }

    if (!data.owned) {
        html += '<button type="button" onclick="buyEmoticonSet(' + data.id + ')" class="btn btn-primary text-sm">' +
                (data.price === 0 ? '무료 받기' : '구매하기') + '</button>';
    }
    html += '</div>';

    container.innerHTML = html;
}

function closeSetDetail() {
    var modal = document.getElementById('emoticonDetailModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

function buyEmoticonSet(esId) {
    if (!confirm('이모티콘 셋을 구매하시겠습니까?')) return;

    var meta = document.querySelector('meta[name="mg-bbs-url"]');
    var bbs = meta ? meta.content : '/bbs';

    var xhr = new XMLHttpRequest();
    xhr.open('POST', bbs + '/emoticon_buy.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function() {
        var res = JSON.parse(xhr.responseText);
        alert(res.message);
        if (res.success) {
            closeSetDetail();
            location.reload();
        }
    };
    xhr.send('es_id=' + esId);
}

function escHtml(str) {
    var d = document.createElement('div');
    d.appendChild(document.createTextNode(str));
    return d.innerHTML;
}
</script>
