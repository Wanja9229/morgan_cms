-- Morgan Edition - 불필요한 테이블 정리
-- 사용 전 백업 권장!

-- SMS5 관련 테이블 삭제 (실수로 설치된 경우)
DROP TABLE IF EXISTS g5_sms5_book;
DROP TABLE IF EXISTS g5_sms5_book_group;
DROP TABLE IF EXISTS g5_sms5_emoticon;
DROP TABLE IF EXISTS g5_sms5_emoticon_group;
DROP TABLE IF EXISTS g5_sms5_history;
DROP TABLE IF EXISTS g5_sms5_history_result;

-- 쇼핑몰 관련 테이블 삭제 (사용 안 함)
DROP TABLE IF EXISTS g5_shop_banner;
DROP TABLE IF EXISTS g5_shop_cart;
DROP TABLE IF EXISTS g5_shop_category;
DROP TABLE IF EXISTS g5_shop_coupon;
DROP TABLE IF EXISTS g5_shop_coupon_log;
DROP TABLE IF EXISTS g5_shop_coupon_zone;
DROP TABLE IF EXISTS g5_shop_default;
DROP TABLE IF EXISTS g5_shop_event;
DROP TABLE IF EXISTS g5_shop_event_item;
DROP TABLE IF EXISTS g5_shop_inicis;
DROP TABLE IF EXISTS g5_shop_item;
DROP TABLE IF EXISTS g5_shop_item_option;
DROP TABLE IF EXISTS g5_shop_item_qa;
DROP TABLE IF EXISTS g5_shop_item_relation;
DROP TABLE IF EXISTS g5_shop_item_stocksms;
DROP TABLE IF EXISTS g5_shop_item_use;
DROP TABLE IF EXISTS g5_shop_order;
DROP TABLE IF EXISTS g5_shop_order_address;
DROP TABLE IF EXISTS g5_shop_order_data;
DROP TABLE IF EXISTS g5_shop_order_delete;
DROP TABLE IF EXISTS g5_shop_order_item;
DROP TABLE IF EXISTS g5_shop_personalpay;
DROP TABLE IF EXISTS g5_shop_pg_log;
DROP TABLE IF EXISTS g5_shop_sendcost;
DROP TABLE IF EXISTS g5_shop_wish;

-- 기타 사용하지 않는 테이블 (선택적)
-- DROP TABLE IF EXISTS g5_poll;
-- DROP TABLE IF EXISTS g5_poll_etc;

-- 확인
SELECT 'Cleanup completed!' AS message;
