DELIMITER $$

CREATE FUNCTION LEYKA_GET_POST_DONATION_TOTAL_FUNDED_AMOUNT(
    donation_id INT UNSIGNED
)
    RETURNS FLOAT
    DETERMINISTIC
BEGIN

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

CREATE FUNCTION LEYKA_GET_SEP_DONATION_TOTAL_FUNDED_AMOUNT(
    donation_id INT UNSIGNED
)
    RETURNS FLOAT
    DETERMINISTIC
BEGIN

    DECLARE result FLOAT;
    SELECT vnwe94nv_leyka_donations.amount_total INTO result FROM vnwe94nv_leyka_donations WHERE vnwe94nv_leyka_donations.ID = donation_id;

    IF (result > 0.0) THEN
        RETURN(result);
    END IF;

    SELECT vnwe94nv_leyka_donations.amount INTO result FROM vnwe94nv_leyka_donations WHERE vnwe94nv_leyka_donations.ID = donation_id;

    IF (result > 0.0) THEN
        RETURN(result);
    ELSE
        SET result = 0.0;
        RETURN(result);
    END IF;

END $$

CREATE FUNCTION LEYKA_GET_CAMPAIGN_TOTAL_FUNDED_AMOUNT_POST(
    campaign_id INT UNSIGNED
)
    RETURNS FLOAT
    DETERMINISTIC
BEGIN

    DECLARE campaign_total_collected FLOAT DEFAULT 0.0;
    DECLARE campaign_donations_cursor_finished INTEGER DEFAULT 0;
    DECLARE donation_id INT UNSIGNED;

    DECLARE cursor_campaign_donation_id
        CURSOR FOR
        SELECT vnwe94nv_posts.ID
        FROM vnwe94nv_posts JOIN vnwe94nv_postmeta ON vnwe94nv_posts.ID = vnwe94nv_postmeta.post_id
        WHERE vnwe94nv_postmeta.meta_key = 'leyka_campaign_id'
          AND vnwe94nv_postmeta.meta_value = campaign_id
          AND vnwe94nv_posts.post_status = 'funded'
          AND vnwe94nv_posts.post_type = 'leyka_donation';

    DECLARE CONTINUE HANDLER
        FOR NOT FOUND SET campaign_donations_cursor_finished = 1;

    OPEN cursor_campaign_donation_id;

    get_donation_total_amount: LOOP
        FETCH cursor_campaign_donation_id INTO donation_id;
        IF campaign_donations_cursor_finished = 1 THEN
            LEAVE get_donation_total_amount;
        END IF;

        SET campaign_total_collected = campaign_total_collected + LEYKA_GET_POST_DONATION_TOTAL_FUNDED_AMOUNT(donation_id);
    END LOOP get_donation_total_amount;

    CLOSE cursor_campaign_donation_id;

    RETURN campaign_total_collected;

END $$

CREATE FUNCTION LEYKA_GET_CAMPAIGN_TOTAL_FUNDED_AMOUNT_SEP(
    campaign_id INT UNSIGNED
)
    RETURNS FLOAT
    DETERMINISTIC
BEGIN

    DECLARE campaign_total_collected FLOAT DEFAULT 0.0;
    DECLARE campaign_donations_cursor_finished INTEGER DEFAULT 0;
    DECLARE donation_id INT UNSIGNED;

    DECLARE cursor_campaign_donation_id
        CURSOR FOR
        SELECT vnwe94nv_leyka_donations.ID
        FROM vnwe94nv_leyka_donations
        WHERE vnwe94nv_leyka_donations.campaign_id = campaign_id
          AND vnwe94nv_leyka_donations.status = 'funded';

    DECLARE CONTINUE HANDLER
        FOR NOT FOUND SET campaign_donations_cursor_finished = 1;

    OPEN cursor_campaign_donation_id;

    get_donation_total_amount: LOOP
        FETCH cursor_campaign_donation_id INTO donation_id;
        IF campaign_donations_cursor_finished = 1 THEN
            LEAVE get_donation_total_amount;
        END IF;

        SET campaign_total_collected = campaign_total_collected + LEYKA_GET_SEP_DONATION_TOTAL_FUNDED_AMOUNT(donation_id);
    END LOOP get_donation_total_amount;

    CLOSE cursor_campaign_donation_id;

    RETURN campaign_total_collected;

END $$

DELIMITER ;