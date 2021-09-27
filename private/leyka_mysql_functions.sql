DELIMITER $$

CREATE FUNCTION LEYKA_GET_POST_DONATION_TOTAL_FUNDED_AMOUNT(
    donation_id INT UNSIGNED
)
    RETURNS FLOAT
    DETERMINISTIC
BEGIN
    -- statements
    DECLARE result FLOAT;
    SELECT vnwe94nv_postmeta.meta_value INTO result FROM vnwe94nv_postmeta WHERE meta_key='leyka_donation_amount_total' AND vnwe94nv_postmeta.post_id=donation_id;

    IF (result > 0.0) THEN
        RETURN(result);
    END IF;

    SELECT vnwe94nv_postmeta.meta_value INTO result FROM vnwe94nv_postmeta WHERE meta_key='leyka_donation_amount' AND vnwe94nv_postmeta.post_id = donation_id;

    IF (result > 0.0) THEN
        RETURN(result);
    ELSE
        SET result = 0.0;
        RETURN(result);
    END IF;

END $$

DELIMITER ;