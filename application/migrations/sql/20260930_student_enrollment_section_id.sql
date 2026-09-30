-- Run once in school_management_system. academic_sections.id is an integer primary key.
ALTER TABLE `student_enrollments`
    ADD COLUMN `section_id` INT NULL AFTER `section`,
    ADD INDEX `idx_student_enrollments_section_id` (`section_id`),
    ADD CONSTRAINT `fk_student_enrollments_academic_section`
        FOREIGN KEY (`section_id`) REFERENCES `academic_sections` (`id`)
        ON UPDATE CASCADE
        ON DELETE RESTRICT;

-- Link existing section names to their section records where the year and grade match.
UPDATE `student_enrollments` AS enrollment
INNER JOIN `academic_sections` AS section
    ON section.`year` = enrollment.`academic_year`
    AND (
        section.`grade` = enrollment.`grade_level`
        OR enrollment.`grade_level` = CONCAT('Grade ', section.`grade`)
        OR (section.`grade` = 'N' AND enrollment.`grade_level` IN ('N', 'Nursery'))
        OR (section.`grade` = 'K' AND enrollment.`grade_level` IN ('K', 'Kindergarten'))
    )
    AND section.`section` = enrollment.`section`
SET enrollment.`section_id` = section.`id`
WHERE enrollment.`section_id` IS NULL
  AND enrollment.`section` IS NOT NULL
  AND TRIM(enrollment.`section`) <> '';

-- Resolve legacy section IDs stored as text only when no section name matched.
UPDATE `student_enrollments` AS enrollment
INNER JOIN `academic_sections` AS section
    ON section.`year` = enrollment.`academic_year`
    AND (
        section.`grade` = enrollment.`grade_level`
        OR enrollment.`grade_level` = CONCAT('Grade ', section.`grade`)
        OR (section.`grade` = 'N' AND enrollment.`grade_level` IN ('N', 'Nursery'))
        OR (section.`grade` = 'K' AND enrollment.`grade_level` IN ('K', 'Kindergarten'))
    )
    AND CAST(section.`id` AS CHAR) = TRIM(enrollment.`section`)
SET enrollment.`section_id` = section.`id`,
    enrollment.`section` = section.`section`
WHERE enrollment.`section_id` IS NULL
  AND enrollment.`section` REGEXP '^[0-9]+$';
