-- 캐릭터 수정 이력 테이블
CREATE TABLE IF NOT EXISTS `mg_character_edit_log` (
    `cel_id` INT AUTO_INCREMENT PRIMARY KEY,
    `ch_id` INT NOT NULL,
    `mb_id` VARCHAR(20) NOT NULL,
    `cel_field` VARCHAR(100) NOT NULL COMMENT '변경된 필드명',
    `cel_old_value` TEXT COMMENT '변경 전 값',
    `cel_new_value` TEXT COMMENT '변경 후 값',
    `cel_created` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_ch_id` (`ch_id`),
    INDEX `idx_mb_id` (`mb_id`),
    INDEX `idx_created` (`cel_created`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
