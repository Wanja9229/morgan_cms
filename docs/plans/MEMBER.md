# 회원 시스템 기획

> **구현 완료** (Phase 1)

> 자캐 커뮤니티 CMS - 회원 시스템 설계
> 기반: 그누보드 최신 버전

> ⚠️ **스키마 참조**: 최종 테이블 구조는 [DB.md](./DB.md) 참조
> 이 문서의 테이블 정의는 설계 참고용이며, 실제 구현 시 DB.md 기준

---

## 개요

- **목표**: 간편 회원가입, 최소한의 개인정보 수집
- **인증**: DB 직접 가입 (소셜 로그인 없음)
- **봇 방지**: CAPTCHA 적용

---

## 회원 테이블 (member)

### 유지 필드

| 필드 | 타입 | 설명 |
|------|------|------|
| mb_no | int AUTO_INCREMENT | PK |
| mb_id | varchar(20) UNIQUE | 아이디 |
| mb_password | varchar(255) | 비밀번호 (해시) |
| mb_nick | varchar(255) | 닉네임 |
| mb_nick_date | date | 닉네임 변경일 (쿨타임용) |
| mb_level | tinyint | 회원 등급 |
| mb_point | int | 포인트 (공용 재화) |
| mb_datetime | datetime | 가입일 |
| mb_ip | varchar(255) | 가입 IP |
| mb_today_login | datetime | 최근 로그인 |
| mb_login_ip | varchar(255) | 로그인 IP |
| mb_last_activity | datetime | 최근 활동 시간 |
| mb_status | varchar(255) | 상태 메시지 (용도 변경) |
| mb_leave_date | varchar(8) | 탈퇴일 |
| mb_intercept_date | varchar(8) | 차단일 |
| mb_memo | text | 관리자 메모 |
| mb_1 ~ mb_10 | varchar(255) | 여분 필드 (확장용) |

> ※ mb_last_activity, mb_status는 검토 후 필요시 추가 예정

### 제거 필드

| 필드 | 제거 이유 |
|------|----------|
| mb_name | 실명 수집 안 함 |
| mb_email | 간편 가입 |
| mb_tel, mb_hp | 불필요 |
| mb_birth, mb_sex | 불필요 |
| mb_zip*, mb_addr* | 배송 기능 없음 |
| mb_certify, mb_adult, mb_dupinfo | 본인인증 없음 |
| mb_email_certify* | 이메일 인증 없음 |
| mb_sms, mb_mailling | 발송 기능 없음 |
| mb_homepage | 사용 안 함 |
| mb_signature | 사용 안 함 |
| mb_profile | 캐릭터 프로필로 대체 |
| mb_open* | 전체 공개 고정 |
| mb_recommend | 추천인 시스템 없음 |
| mb_board_call, mb_memo_call, mb_board_link | 구조 변경 |

---

## 정책

### 가입
- 아이디 + 비밀번호 + 닉네임만 필수
- CAPTCHA로 봇 방지
- 이메일/휴대폰 수집 안 함

### 닉네임
- 중복 불가
- 변경 주기: 관리자 설정 가능 (mb_nick_date로 체크)

### 탈퇴
- 탈퇴 시 아이디 보존 (mb_leave_date 기록)
- 동일 아이디 재가입 불가
- 관리자 권한으로 완전 삭제 가능

### 프로필 공개
- 계정 프로필은 전체 공개 고정
- 상세 프로필은 캐릭터 단위로 관리

---

## 포인트 시스템

### 구조: 하이브리드 방식

```
유저 (member)
├─ mb_point: 공용 포인트 (상점 재화)
│
└─ 캐릭터 (character)
   ├─ ch_exp: 경험치/활동량 (캐릭터별)
   └─ ch_exp: ...
```

### 유저 포인트 (mb_point)
- 상점 구매용 공용 재화
- 커뮤니티 활동으로 적립
- 계정 단위 관리

### 캐릭터 경험치 (ch_exp)
- 캐릭터별 활동량
- RP/창작 활동으로 적립
- 캐릭터 단위 관리

### 상점 아이템 (예정)
- 커뮤니티 기능: 칭호, 이모티콘, 캐릭터 슬롯 등
- 세계관 아이템: 유저 합의 기반 (시스템 외)

---

## 관련 테이블

### 기존 활용
- `member` - 회원 정보
- `point` - 포인트 내역 로그
- `login` - 로그인 기록

### 신규/수정 예정
- `character` - 캐릭터 정보 (ch_exp 포함)
- `member_social` - 제거 (소셜 로그인 없음)

---

## 관리자 기능

- 회원 목록/검색/수정
- 회원 차단/해제
- 탈퇴 회원 완전 삭제
- 포인트 수동 지급/차감
- 닉네임 변경 주기 설정

---

## 보안

- 비밀번호: bcrypt 해시
- 봇 방지: CAPTCHA (reCAPTCHA / hCaptcha / 자체)
- 세션 기반 인증 (기존 유지)
- IP 로깅

---

## TODO

- [x] 테이블 스키마 확정 → PLAN_DB.md
- [ ] 가입 폼 UI 설계
- [ ] CAPTCHA 방식 결정
- [ ] 관리자 페이지 설계
- [ ] 캐릭터 테이블 연동 설계

---

*작성일: 2026-02-02*
*수정일: 2026-02-03*
*상태: 1차 기획 완료, 문서 검토 완료*
