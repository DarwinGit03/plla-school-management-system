-- Run once to convert existing grade codes to the same display labels now saved by enrollment.
UPDATE `student_enrollments`
SET `grade_level` = CASE
    WHEN UPPER(TRIM(`grade_level`)) = 'N' THEN 'Nursery'
    WHEN UPPER(TRIM(`grade_level`)) = 'K' THEN 'Kindergarten'
    WHEN TRIM(`grade_level`) REGEXP '^[0-9]+$' THEN CONCAT('Grade ', TRIM(`grade_level`))
    ELSE TRIM(`grade_level`)
END
WHERE UPPER(TRIM(`grade_level`)) IN ('N', 'K')
   OR TRIM(`grade_level`) REGEXP '^[0-9]+$';
