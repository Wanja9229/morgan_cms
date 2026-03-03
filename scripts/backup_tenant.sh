#!/bin/bash
# ============================================================
# Morgan CMS — 테넌트별 백업 스크립트
#
# 기능:
#   - 마스터 DB에서 활성 테넌트 목록 조회
#   - 각 테넌트 DB mysqldump
#   - 테넌트 data 디렉토리 tar.gz
#   - 마스터 DB 백업
#   - 7일 로테이션
#
# cron 설정 (매일 새벽 3시):
#   0 3 * * * /path/to/backup_tenant.sh >> /var/log/morgan_backup.log 2>&1
#
# 환경변수:
#   MASTER_DB_HOST, MASTER_DB_USER, MASTER_DB_PASS, MASTER_DB_NAME
#   BACKUP_DIR (기본: /var/backups/morgan)
#   DATA_DIR (기본: /var/www/html/data)
#   RETENTION_DAYS (기본: 7)
# ============================================================

set -euo pipefail

# 기본값
BACKUP_DIR="${BACKUP_DIR:-/var/backups/morgan}"
DATA_DIR="${DATA_DIR:-/var/www/html/data}"
RETENTION_DAYS="${RETENTION_DAYS:-7}"

# 마스터 DB 접속 정보
MASTER_DB_HOST="${MASTER_DB_HOST:-localhost}"
MASTER_DB_USER="${MASTER_DB_USER:-}"
MASTER_DB_PASS="${MASTER_DB_PASS:-}"
MASTER_DB_NAME="${MASTER_DB_NAME:-mg_master}"

DATE=$(date +%Y%m%d_%H%M%S)
TODAY_DIR="${BACKUP_DIR}/${DATE}"

echo "=== Morgan Backup Start: ${DATE} ==="

# 필수 체크
if [ -z "$MASTER_DB_USER" ] || [ -z "$MASTER_DB_PASS" ]; then
    echo "ERROR: MASTER_DB_USER and MASTER_DB_PASS must be set"
    exit 1
fi

# 백업 디렉토리 생성
mkdir -p "${TODAY_DIR}"

# 1. 마스터 DB 백업
echo "[1/3] Backing up master DB..."
mysqldump -h "${MASTER_DB_HOST}" -u "${MASTER_DB_USER}" -p"${MASTER_DB_PASS}" \
    --single-transaction --routines --triggers \
    "${MASTER_DB_NAME}" | gzip > "${TODAY_DIR}/master_${MASTER_DB_NAME}.sql.gz"
echo "  -> master DB done"

# 2. 테넌트 DB 백업
echo "[2/3] Backing up tenant DBs..."
TENANT_LIST=$(mysql -h "${MASTER_DB_HOST}" -u "${MASTER_DB_USER}" -p"${MASTER_DB_PASS}" \
    -N -e "SELECT id, subdomain, db_name, db_user, db_pass FROM ${MASTER_DB_NAME}.tenants WHERE status != 'deleted'" 2>/dev/null)

TENANT_COUNT=0
while IFS=$'\t' read -r t_id t_sub t_db t_user t_pass; do
    [ -z "$t_id" ] && continue
    echo "  Tenant #${t_id} (${t_sub}): DB=${t_db}"

    # DB 덤프
    mysqldump -h "${MASTER_DB_HOST}" -u "${t_user}" -p"${t_pass}" \
        --single-transaction --routines --triggers \
        "${t_db}" 2>/dev/null | gzip > "${TODAY_DIR}/tenant_${t_id}_${t_sub}_db.sql.gz"

    # Data 디렉토리 백업
    TENANT_DATA="${DATA_DIR}/tenants/${t_id}"
    if [ -d "${TENANT_DATA}" ]; then
        tar czf "${TODAY_DIR}/tenant_${t_id}_${t_sub}_data.tar.gz" \
            -C "${DATA_DIR}/tenants" "${t_id}" 2>/dev/null || true
    fi

    TENANT_COUNT=$((TENANT_COUNT + 1))
done <<< "${TENANT_LIST}"

echo "  -> ${TENANT_COUNT} tenants backed up"

# 3. 오래된 백업 정리
echo "[3/3] Cleaning old backups (>${RETENTION_DAYS} days)..."
find "${BACKUP_DIR}" -maxdepth 1 -type d -mtime +${RETENTION_DAYS} -exec rm -rf {} \; 2>/dev/null || true
echo "  -> cleanup done"

# 요약
BACKUP_SIZE=$(du -sh "${TODAY_DIR}" | cut -f1)
echo "=== Backup Complete: ${TODAY_DIR} (${BACKUP_SIZE}) ==="
echo "=== ${TENANT_COUNT} tenants + master DB backed up ==="
