DELIMITER //

CREATE PROCEDURE EditSubmissions(
    IN p_id INT,
    IN p_tree_type VARCHAR(50),
    IN p_number INT,
    IN p_date_planted DATE,
    IN p_location VARCHAR(100),
    IN p_image_path VARCHAR(255)
)
BEGIN
    -- Calculate CO2 absorption rate based on tree type
    DECLARE co2_rate DECIMAL(10,2);
    
    CASE p_tree_type
        WHEN 'Narra' THEN SET co2_rate = 21.8;
        WHEN 'Mahogany' THEN SET co2_rate = 28.0;
        WHEN 'Molave' THEN SET co2_rate = 25.0;
        WHEN 'Acacia' THEN SET co2_rate = 35.0;
        WHEN 'Yakal' THEN SET co2_rate = 20.0;
        WHEN 'Ipil-ipil' THEN SET co2_rate = 15.0;
        WHEN 'Bamboo' THEN SET co2_rate = 62.0;
        WHEN 'Banaba' THEN SET co2_rate = 22.5;
        WHEN 'Talisay' THEN SET co2_rate = 18.0;
        WHEN 'Balete' THEN SET co2_rate = 30.0;
        ELSE SET co2_rate = 0;
    END CASE;

    -- Update the tree planting record
    UPDATE tree_planting 
    SET 
        tree_type_id = tree_type_id,
        number = p_number,
        updated_at = NOW(),
        location = p_location,
        co2_per_tree = co2_rate,
        total_co2 = (p_number * co2_rate),
        image_path = IF(p_image_path = '', image_path, p_image_path),
        status = 'Pending'
    WHERE id = p_id;
END //

CREATE PROCEDURE ViewSubmissions(
    IN p_start INT,
    IN p_limit INT,
    IN p_year INT,
    IN p_month INT
)
BEGIN
    SELECT 
        t.id,
        r.username,
        r.email,
        r.group_members,
        t.tree_type,
        t.number,
        t.date_planted,
        t.location,
        t.CO2,
        t.total_CO2 as total_Co2,
        t.status,
        t.image_path
    FROM tree_planting t
    JOIN registeredacc r ON t.user_id = r.user_id
    WHERE 
        (p_year IS NULL OR YEAR(t.date_planted) = p_year)
        AND
        (p_month IS NULL OR MONTH(t.date_planted) = p_month)
    ORDER BY t.date_planted DESC
    LIMIT p_start, p_limit;
END //

CREATE PROCEDURE GetAllTreePlantingLocations()
BEGIN
    SELECT 
        CASE 
            WHEN tp.location = 'OTHER' THEN tp.other_location
            ELSE tp.location
        END AS location,
        SUM(tp.number) AS total_trees_planted
        SUM(t.total_co2) as total_Co2
    FROM 
        tree_plantings tp
    WHERE 
        tp.status = 'Approved'
    GROUP BY 
        CASE 
            WHEN tp.location = 'OTHER' THEN tp.other_location
            ELSE tp.location
        END;
END //

DELIMITER ; 