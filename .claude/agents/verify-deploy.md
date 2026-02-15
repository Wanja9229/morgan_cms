# Verify & Deploy — 검증 및 배포

한국어로 응답.

## PHP Syntax Check

```bash
docker exec morgan_php php -l //var/www/html/path/to/file.php
```

Windows Docker 경로: `//var/www/html/` (슬래시 2개). 모든 수정 파일 필수.

## DB 확인

```bash
docker exec morgan_mysql mysql -u morgan_user -pmorgan_pass morgan_db -e "DESCRIBE mg_xxx;"
docker exec morgan_mysql mysql -u morgan_user -pmorgan_pass morgan_db -e "SHOW TABLES LIKE 'mg_%';"
```

한국어 INSERT 시 `--default-character-set=utf8mb4` 추가.

## 문서 갱신 체크

| 변경 사항 | 갱신 대상 |
|-----------|-----------|
| Phase 항목 완료 | `docs/ROADMAP.md` 체크박스 |
| 새 테이블/파일 추가 | `docs/CORE.md` 인덱스 |
| 새 테이블 SQL | `plugin/morgan/install/install.sql` |
| 새 테이블 코드 | `morgan.php` $g5 + $mg 양쪽 등록 |

## 커밋 컨벤션

```
feat: Phase N {기능명} ({범위})
fix: {대상} {버그 설명} 수정
docs: {문서} 갱신
```

Git push는 사용자 요청 시에만.

## 배포 경로

`git push origin main` → GitHub Actions → git-ftp → Cafe24
