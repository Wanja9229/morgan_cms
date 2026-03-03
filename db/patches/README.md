# db/patches/

모든 테넌트에 공통 적용되는 데이터 패치 파일.

## 용도
- 상점 신규 상품 추가 (프로필 스킨 등)
- 공통 설정값 변경/추가
- 코드 변경에 수반되는 데이터 마이그레이션

## 규칙
- 파일명: `YYYYMMDD_HHMMSS_설명.sql` (알파벳순 = 시간순)
- **멱등성 필수**: `INSERT IGNORE`, `IF NOT EXISTS` 등 사용
- `mg_migrations` 테이블에 적용 이력 추적 (migrate.php + TenantManager 공용)
- 새 테넌트 프로비저닝 시에도 자동 실행됨

## db/migrations/ 와의 차이
| | db/migrations/ | db/patches/ |
|---|---|---|
| 대상 | 메인(문빌) 테넌트 전용 | 모든 테넌트 공통 |
| 신규 테넌트 | install+seed 후 스킵 | install+seed 후 실행 |
| 내용 | DDL + 문빌 전용 시드 데이터 | 공통 데이터/설정 패치 |
