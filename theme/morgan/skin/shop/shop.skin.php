<?php
/**
 * Morgan Edition - 상점 목록 스킨
 */

if (!defined('_GNUBOARD_')) exit;

// 타입 라벨은 morgan.php에서 가져옴
$item_type_names = $type_labels;
?>

<div class="mg-inner">
    <!-- 상단: 내 포인트 & 제목 -->
    <div class="flex items-center justify-between mb-6 flex-wrap gap-4">
        <h1 class="text-2xl font-bold text-mg-text-primary flex items-center gap-2">
            <svg class="w-6 h-6 text-mg-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
            </svg>
            상점
        </h1>
        <div class="card px-4 py-2">
            <span class="text-mg-text-muted text-sm">내 포인트</span>
            <span class="text-mg-accent font-bold ml-2"><?php echo mg_point_format($my_point); ?></span>
        </div>
    </div>

    <!-- 타입 그룹 탭 -->
    <div class="mb-6 overflow-x-auto">
        <div class="flex gap-2 min-w-max">
            <a href="<?php echo G5_BBS_URL; ?>/shop.php" class="px-4 py-2 rounded-lg font-medium transition-colors <?php echo (!$is_emoticon_tab && empty($tab)) ? 'bg-mg-accent text-white' : 'bg-mg-bg-secondary text-mg-text-secondary hover:bg-mg-bg-tertiary'; ?>">
                전체
            </a>
            <?php foreach ($type_groups as $group_key => $group) { ?>
            <a href="<?php echo G5_BBS_URL; ?>/shop.php?tab=<?php echo $group_key; ?>" class="px-4 py-2 rounded-lg font-medium transition-colors flex items-center gap-1 <?php echo (!$is_emoticon_tab && $tab === $group_key) ? 'bg-mg-accent text-white' : 'bg-mg-bg-secondary text-mg-text-secondary hover:bg-mg-bg-tertiary'; ?>">
                <?php echo mg_icon($group['icon'], 'w-4 h-4'); ?>
                <?php echo htmlspecialchars($group['label']); ?>
            </a>
            <?php } ?>
            <?php if ($emoticon_use == '1') { ?>
            <a href="<?php echo G5_BBS_URL; ?>/shop.php?tab=emoticon" class="px-4 py-2 rounded-lg font-medium transition-colors flex items-center gap-1 <?php echo $is_emoticon_tab ? 'bg-mg-accent text-white' : 'bg-mg-bg-secondary text-mg-text-secondary hover:bg-mg-bg-tertiary'; ?>">
                <?php echo mg_icon('face-smile', 'w-4 h-4'); ?>
                이모티콘
            </a>
            <?php } ?>
        </div>
    </div>

    <?php if ($is_emoticon_tab) { ?>
    <!-- 이모티콘 탭 콘텐츠 -->

    <!-- 정렬 -->
    <div class="flex gap-2 mb-4">
        <a href="?tab=emoticon&sort=latest" class="px-3 py-1.5 rounded text-sm <?php echo $sort === 'latest' ? 'bg-mg-accent text-white' : 'bg-mg-bg-tertiary text-mg-text-secondary hover:text-mg-text-primary'; ?>">최신순</a>
        <a href="?tab=emoticon&sort=popular" class="px-3 py-1.5 rounded text-sm <?php echo $sort === 'popular' ? 'bg-mg-accent text-white' : 'bg-mg-bg-tertiary text-mg-text-secondary hover:text-mg-text-primary'; ?>">인기순</a>
        <a href="?tab=emoticon&sort=free" class="px-3 py-1.5 rounded text-sm <?php echo $sort === 'free' ? 'bg-mg-accent text-white' : 'bg-mg-bg-tertiary text-mg-text-secondary hover:text-mg-text-primary'; ?>">무료</a>
    </div>

    <?php if (!empty($emoticon_sets)) { ?>
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
        <?php foreach ($emoticon_sets as $eset) {
            $owned = mg_owns_emoticon_set($member['mb_id'], $eset['es_id']);
        ?>
        <div class="card p-0 overflow-hidden cursor-pointer hover:ring-2 hover:ring-mg-accent transition-all group" onclick="showSetDetail(<?php echo $eset['es_id']; ?>)">
            <div class="aspect-square bg-mg-bg-tertiary relative overflow-hidden flex items-center justify-center">
                <?php if ($eset['es_preview']) { ?>
                <img src="<?php echo htmlspecialchars($eset['es_preview']); ?>" alt="" class="w-3/4 h-3/4 object-contain group-hover:scale-110 transition-transform">
                <?php } else { ?>
                <svg class="w-12 h-12 text-mg-text-muted/30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10" stroke-width="1.5"/>
                    <path stroke-linecap="round" stroke-width="1.5" d="M8 14s1.5 2 4 2 4-2 4-2"/>
                    <circle cx="9" cy="10" r="1" fill="currentColor" stroke="none"/>
                    <circle cx="15" cy="10" r="1" fill="currentColor" stroke="none"/>
                </svg>
                <?php } ?>
                <?php if ($owned) { ?>
                <span class="absolute top-2 right-2 px-2 py-0.5 bg-mg-success text-white text-xs font-bold rounded">보유중</span>
                <?php } ?>
            </div>
            <div class="p-3">
                <h3 class="font-medium text-mg-text-primary text-center truncate"><?php echo htmlspecialchars($eset['es_name']); ?></h3>
                <?php if ($eset['es_creator_id']) { ?>
                <p class="text-xs text-mg-text-muted text-center mt-0.5">by <?php echo htmlspecialchars($eset['es_creator_id']); ?></p>
                <?php } ?>
                <div class="flex items-center justify-between mt-2">
                    <span class="text-xs text-mg-text-muted"><?php echo (int)$eset['em_count']; ?>개</span>
                    <?php if ($owned) { ?>
                    <span class="text-xs text-mg-success font-bold">보유중</span>
                    <?php } elseif ((int)$eset['es_price'] === 0) { ?>
                    <span class="text-xs font-bold text-mg-success">무료</span>
                    <?php } else { ?>
                    <span class="text-xs font-bold text-mg-accent"><?php echo number_format((int)$eset['es_price']); ?>P</span>
                    <?php } ?>
                </div>
            </div>
        </div>
        <?php } ?>
    </div>

    <!-- 페이지네이션 -->
    <?php if ($total_page > 1) { ?>
    <div class="mt-8 flex justify-center gap-1">
        <?php
        $qs = 'tab=emoticon&sort=' . urlencode($sort);
        $start_page = max(1, $page - 2);
        $end_page = min($total_page, $page + 2);
        if ($page > 1) {
            echo '<a href="?'.$qs.'&page='.($page-1).'" class="px-3 py-2 bg-mg-bg-secondary rounded hover:bg-mg-bg-tertiary">&lsaquo;</a>';
        }
        for ($i = $start_page; $i <= $end_page; $i++) {
            if ($i == $page) echo '<span class="px-3 py-2 bg-mg-accent text-white rounded">'.$i.'</span>';
            else echo '<a href="?'.$qs.'&page='.$i.'" class="px-3 py-2 bg-mg-bg-secondary rounded hover:bg-mg-bg-tertiary">'.$i.'</a>';
        }
        if ($page < $total_page) {
            echo '<a href="?'.$qs.'&page='.($page+1).'" class="px-3 py-2 bg-mg-bg-secondary rounded hover:bg-mg-bg-tertiary">&rsaquo;</a>';
        }
        ?>
    </div>
    <?php } ?>

    <?php } else { ?>
    <div class="card py-16 text-center">
        <svg class="w-16 h-16 mx-auto text-mg-text-muted/30 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <circle cx="12" cy="12" r="10" stroke-width="1.5"/>
            <path stroke-linecap="round" stroke-width="1.5" d="M8 14s1.5 2 4 2 4-2 4-2"/>
            <circle cx="9" cy="10" r="1" fill="currentColor" stroke="none"/>
            <circle cx="15" cy="10" r="1" fill="currentColor" stroke="none"/>
        </svg>
        <p class="text-mg-text-muted">아직 등록된 이모티콘 셋이 없습니다.</p>
    </div>
    <?php } ?>

    <!-- 셋 상세 모달 -->
    <div id="emoticonDetailModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/60" onclick="if(event.target===this) closeSetDetail();">
        <div class="bg-mg-bg-secondary border border-mg-bg-tertiary rounded-xl max-w-lg w-11/12 max-h-[80vh] overflow-y-auto p-5">
            <div id="emoticonDetailContent">
                <div class="text-center py-8 text-mg-text-muted">로딩중...</div>
            </div>
        </div>
    </div>

    <?php } else { ?>
    <!-- 일반 상품 그리드 -->
    <?php if (count($items) > 0) { ?>
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
        <?php foreach ($items as $item) {
            $status = mg_get_item_status($item);
            $is_available = ($status == 'selling');
            $stock_text = '';
            if ($item['si_stock'] > 0) {
                $remain = $item['si_stock'] - $item['si_stock_sold'];
                $stock_text = "남은 수량: {$remain}개";
            }
        ?>
        <a href="<?php echo G5_BBS_URL; ?>/shop_view.php?si_id=<?php echo $item['si_id']; ?>" class="card p-0 overflow-hidden group hover:ring-2 hover:ring-mg-accent transition-all <?php echo !$is_available ? 'opacity-60' : ''; ?>">
            <!-- 이미지 -->
            <div class="aspect-square bg-mg-bg-tertiary relative overflow-hidden">
                <?php if ($item['si_image']) { ?>
                <img src="<?php echo $item['si_image']; ?>" alt="" class="w-full h-full object-cover group-hover:scale-105 transition-transform">
                <?php } else { ?>
                <div class="w-full h-full flex items-center justify-center">
                    <svg class="w-12 h-12 text-mg-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
                <?php } ?>

                <!-- 상태 배지 -->
                <?php if ($status != 'selling') { ?>
                <div class="absolute inset-0 bg-black/50 flex items-center justify-center">
                    <?php if ($status == 'sold_out') { ?>
                    <span class="px-3 py-1 bg-mg-error text-white text-sm font-bold rounded">SOLD OUT</span>
                    <?php } elseif ($status == 'coming_soon') { ?>
                    <span class="px-3 py-1 bg-mg-accent text-white text-sm font-bold rounded">COMING SOON</span>
                    <?php } elseif ($status == 'ended') { ?>
                    <span class="px-3 py-1 bg-mg-bg-tertiary text-mg-text-muted text-sm font-bold rounded">판매 종료</span>
                    <?php } ?>
                </div>
                <?php } ?>

                <!-- 타입 배지 -->
                <span class="absolute top-2 left-2 px-2 py-0.5 bg-mg-bg-primary/80 text-xs text-mg-text-muted rounded">
                    <?php echo $item_type_names[$item['si_type']] ?? $item['si_type']; ?>
                </span>
            </div>

            <!-- 정보 -->
            <div class="p-3">
                <h3 class="font-medium text-mg-text-primary truncate"><?php echo htmlspecialchars($item['si_name']); ?></h3>
                <div class="mt-1 flex items-center justify-between">
                    <span class="text-mg-accent font-bold"><?php echo mg_point_format($item['si_price']); ?></span>
                    <?php if ($stock_text) { ?>
                    <span class="text-xs text-mg-text-muted"><?php echo $stock_text; ?></span>
                    <?php } ?>
                </div>
            </div>
        </a>
        <?php } ?>
    </div>

    <!-- 페이지네이션 -->
    <?php if ($total_page > 1) { ?>
    <div class="mt-8 flex justify-center gap-1">
        <?php
        $query = $tab ? "tab={$tab}&" : '';
        $start_page = max(1, $page - 2);
        $end_page = min($total_page, $page + 2);

        if ($page > 1) {
            echo '<a href="?'.$query.'page=1" class="px-3 py-2 bg-mg-bg-secondary rounded hover:bg-mg-bg-tertiary">&laquo;</a>';
            echo '<a href="?'.$query.'page='.($page-1).'" class="px-3 py-2 bg-mg-bg-secondary rounded hover:bg-mg-bg-tertiary">&lsaquo;</a>';
        }

        for ($i = $start_page; $i <= $end_page; $i++) {
            if ($i == $page) {
                echo '<span class="px-3 py-2 bg-mg-accent text-white rounded">'.$i.'</span>';
            } else {
                echo '<a href="?'.$query.'page='.$i.'" class="px-3 py-2 bg-mg-bg-secondary rounded hover:bg-mg-bg-tertiary">'.$i.'</a>';
            }
        }

        if ($page < $total_page) {
            echo '<a href="?'.$query.'page='.($page+1).'" class="px-3 py-2 bg-mg-bg-secondary rounded hover:bg-mg-bg-tertiary">&rsaquo;</a>';
            echo '<a href="?'.$query.'page='.$total_page.'" class="px-3 py-2 bg-mg-bg-secondary rounded hover:bg-mg-bg-tertiary">&raquo;</a>';
        }
        ?>
    </div>
    <?php } ?>

    <?php } else { ?>
    <!-- 상품 없음 -->
    <div class="card py-16 text-center">
        <svg class="w-16 h-16 mx-auto text-mg-text-muted mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
        </svg>
        <p class="text-mg-text-muted">
            <?php echo $tab ? '해당 카테고리에 상품이 없습니다.' : '등록된 상품이 없습니다.'; ?>
        </p>
    </div>
    <?php } ?>
    <?php } ?>

    <!-- 하단 링크 -->
    <div class="mt-6 flex gap-4 justify-center">
        <a href="<?php echo G5_BBS_URL; ?>/inventory.php" class="text-mg-text-muted hover:text-mg-accent transition-colors flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
            </svg>
            내 인벤토리
        </a>
        <a href="<?php echo G5_BBS_URL; ?>/gift.php" class="text-mg-text-muted hover:text-mg-accent transition-colors flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"/>
            </svg>
            선물함
        </a>
    </div>
</div>

<script>
<?php if ($is_emoticon_tab) { ?>
function showSetDetail(esId) {
    var modal = document.getElementById('emoticonDetailModal');
    var content = document.getElementById('emoticonDetailContent');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    content.innerHTML = '<div class="text-center py-8 text-mg-text-muted">로딩중...</div>';

    var xhr = new XMLHttpRequest();
    xhr.open('GET', '<?php echo G5_BBS_URL; ?>/emoticon_api.php?action=set_detail&es_id=' + esId, true);
    xhr.onload = function() {
        if (xhr.status === 200) {
            var data = JSON.parse(xhr.responseText);
            if (data.error) {
                content.innerHTML = '<p class="text-mg-error text-center py-4">' + escHtml(data.error) + '</p>';
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

    html += '<div class="grid grid-cols-6 gap-2 mb-4 bg-mg-bg-primary rounded-lg p-3">';
    data.emoticons.forEach(function(em) {
        html += '<div class="flex items-center justify-center p-1" title="' + escHtml(em.code) + '">';
        html += '<img src="' + escHtml(em.image) + '" alt="' + escHtml(em.code) + '" class="w-10 h-10 object-contain">';
        html += '</div>';
    });
    html += '</div>';

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

    var xhr = new XMLHttpRequest();
    xhr.open('POST', '<?php echo G5_BBS_URL; ?>/emoticon_buy.php', true);
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
<?php } ?>
</script>
