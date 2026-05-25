SET search_path TO retail;

-- ============================================================
-- Seed base para entorno local / examen
-- Sin insertar IDs autoincrementales manualmente
-- ============================================================

-- 1. Catalogo maestro
INSERT INTO countries (name, iso_code, currency) VALUES
    ('Spain', 'ES', 'EUR'),
    ('France', 'FR', 'EUR'),
    ('Portugal', 'PT', 'EUR');

INSERT INTO brands (name, active) VALUES
    ('Aether', TRUE),
    ('Nordline', TRUE),
    ('Velora', TRUE);

INSERT INTO categories (name) VALUES
    ('Lighting'),
    ('Furniture'),
    ('Outdoor');

INSERT INTO series (name) VALUES
    ('Urban'),
    ('Minimal'),
    ('Terra');

INSERT INTO materials (name) VALUES
    ('Steel'),
    ('Wood'),
    ('Glass');

-- 2. Carriers
INSERT INTO carriers (name, email, phone, active, observations) VALUES
    ('Iberia Supply', 'ops@iberiasupply.test', '+34 910 000 001', TRUE, 'Carrier principal para peninsula iberica'),
    ('Hexa Distribution', 'hello@hexadist.test', '+33 140 000 002', TRUE, 'Opera catalogo premium para Francia');

-- 3. Carrier x country
INSERT INTO carrier_countries (carrier_id, country_id, base_discount_rate, base_delivery_days, active)
SELECT c.id, co.id, v.base_discount_rate, v.base_delivery_days, TRUE
FROM (
    VALUES
        ('Iberia Supply', 'ES', 5.00::DECIMAL(5,2), 3),
        ('Iberia Supply', 'FR', 4.50::DECIMAL(5,2), 5),
        ('Iberia Supply', 'PT', 6.00::DECIMAL(5,2), 2),
        ('Hexa Distribution', 'ES', 3.00::DECIMAL(5,2), 6),
        ('Hexa Distribution', 'FR', 7.00::DECIMAL(5,2), 3)
) AS v(carrier_name, iso_code, base_discount_rate, base_delivery_days)
JOIN carriers c ON c.name = v.carrier_name
JOIN countries co ON co.iso_code = v.iso_code;

-- 4. Carrier x brand
INSERT INTO carrier_brands (carrier_id, brand_id, active)
SELECT c.id, b.id, TRUE
FROM (
    VALUES
        ('Iberia Supply', 'Aether'),
        ('Iberia Supply', 'Nordline'),
        ('Iberia Supply', 'Velora'),
        ('Hexa Distribution', 'Aether'),
        ('Hexa Distribution', 'Velora')
) AS v(carrier_name, brand_name)
JOIN carriers c ON c.name = v.carrier_name
JOIN brands b ON b.name = v.brand_name;

-- 5. Productos
INSERT INTO products (
    carrier_id, reference, ean_13, name, description,
    brand_id, category_id, series_id, material_id,
    weight_kg, volume_m3, active, created_at
)
SELECT
    c.id,
    v.reference,
    v.ean_13,
    v.product_name,
    v.description,
    b.id,
    cat.id,
    s.id,
    m.id,
    v.weight_kg,
    v.volume_m3,
    TRUE,
    v.created_at
FROM (
    VALUES
        ('Iberia Supply', 'AE-LAMP-001', '7501234567890', 'Aether Desk Lamp', 'Compact desk lamp with warm LED light', 'Aether', 'Lighting', 'Minimal', 'Steel', 1.200::DECIMAL(10,3), 0.018::DECIMAL(10,3), NOW() - INTERVAL '30 days'),
        ('Iberia Supply', 'ND-CHAIR-002', '7501234567891', 'Nordline Chair', 'Oak chair for dining rooms', 'Nordline', 'Furniture', 'Urban', 'Wood', 6.500::DECIMAL(10,3), 0.220::DECIMAL(10,3), NOW() - INTERVAL '25 days'),
        ('Iberia Supply', 'VL-TABLE-003', '7501234567892', 'Velora Side Table', 'Glass side table for living rooms', 'Velora', 'Furniture', 'Terra', 'Glass', 8.750::DECIMAL(10,3), 0.160::DECIMAL(10,3), NOW() - INTERVAL '20 days'),
        ('Hexa Distribution', 'AE-OUT-004', '7501234567893', 'Aether Outdoor Lantern', 'Outdoor lantern with weather protection', 'Aether', 'Outdoor', 'Terra', 'Steel', 2.100::DECIMAL(10,3), 0.040::DECIMAL(10,3), NOW() - INTERVAL '18 days'),
        ('Hexa Distribution', 'VL-MIRROR-005', '7501234567894', 'Velora Mirror', 'Decorative wall mirror', 'Velora', 'Furniture', 'Minimal', 'Glass', 5.400::DECIMAL(10,3), 0.095::DECIMAL(10,3), NOW() - INTERVAL '12 days')
) AS v(
    carrier_name, reference, ean_13, product_name, description,
    brand_name, category_name, series_name, material_name,
    weight_kg, volume_m3, created_at
)
JOIN carriers c ON c.name = v.carrier_name
JOIN brands b ON b.name = v.brand_name
JOIN categories cat ON cat.name = v.category_name
JOIN series s ON s.name = v.series_name
JOIN materials m ON m.name = v.material_name;

-- 6. Grupos de productos
INSERT INTO product_groups (carrier_id, name)
SELECT c.id, v.group_name
FROM (
    VALUES
        ('Iberia Supply', 'Best sellers ES'),
        ('Iberia Supply', 'Furniture core'),
        ('Hexa Distribution', 'Premium outdoor')
) AS v(carrier_name, group_name)
JOIN carriers c ON c.name = v.carrier_name;

INSERT INTO product_group_assignments (product_id, group_id)
SELECT p.id, pg.id
FROM (
    VALUES
        ('AE-LAMP-001', 'Best sellers ES'),
        ('ND-CHAIR-002', 'Furniture core'),
        ('VL-TABLE-003', 'Furniture core'),
        ('AE-OUT-004', 'Premium outdoor')
) AS v(reference, group_name)
JOIN products p ON p.reference = v.reference
JOIN product_groups pg ON pg.name = v.group_name;

-- 7. Precios operativos
INSERT INTO product_prices (
    carrier_id, product_id, country_id,
    base_price, final_price, stock_quantity, delivery_days, status, updated_at
)
SELECT
    c.id,
    p.id,
    co.id,
    v.base_price,
    v.final_price,
    v.stock_quantity,
    v.delivery_days,
    'active',
    v.updated_at
FROM (
    VALUES
        ('Iberia Supply', 'AE-LAMP-001', 'ES', 120.00::DECIMAL(12,2), 114.00::DECIMAL(12,2), 45, 3, NOW() - INTERVAL '2 days'),
        ('Iberia Supply', 'AE-LAMP-001', 'FR', 123.00::DECIMAL(12,2), 117.47::DECIMAL(12,2), 20, 5, NOW() - INTERVAL '2 days'),
        ('Iberia Supply', 'ND-CHAIR-002', 'ES', 240.00::DECIMAL(12,2), 225.60::DECIMAL(12,2), 14, 4, NOW() - INTERVAL '1 days'),
        ('Iberia Supply', 'VL-TABLE-003', 'PT', 180.00::DECIMAL(12,2), 169.20::DECIMAL(12,2), 11, 2, NOW() - INTERVAL '1 days'),
        ('Hexa Distribution', 'AE-OUT-004', 'FR', 98.00::DECIMAL(12,2), 91.14::DECIMAL(12,2), 26, 3, NOW() - INTERVAL '3 days'),
        ('Hexa Distribution', 'VL-MIRROR-005', 'ES', 210.00::DECIMAL(12,2), 203.70::DECIMAL(12,2), 7, 6, NOW() - INTERVAL '4 days')
) AS v(carrier_name, reference, iso_code, base_price, final_price, stock_quantity, delivery_days, updated_at)
JOIN carriers c ON c.name = v.carrier_name
JOIN products p ON p.reference = v.reference AND p.carrier_id = c.id
JOIN countries co ON co.iso_code = v.iso_code;

-- 8. Descuentos
INSERT INTO discounts_products (
    carrier_id, product_id, country_id, percentage, valid_from, valid_until
)
SELECT c.id, p.id, co.id, 8.00, CURRENT_DATE - 15, CURRENT_DATE + 30
FROM carriers c
JOIN products p ON p.carrier_id = c.id AND p.reference = 'AE-LAMP-001'
JOIN countries co ON co.iso_code = 'ES'
WHERE c.name = 'Iberia Supply';

INSERT INTO discounts_groups (
    group_id, country_id, percentage, valid_from, valid_until
)
SELECT pg.id, co.id, v.percentage, CURRENT_DATE - v.from_offset, CURRENT_DATE + v.until_offset
FROM (
    VALUES
        ('Furniture core', 'ES', 6.00::DECIMAL(5,2), 10, 40),
        ('Premium outdoor', 'FR', 5.50::DECIMAL(5,2), 5, 25)
) AS v(group_name, iso_code, percentage, from_offset, until_offset)
JOIN product_groups pg ON pg.name = v.group_name
JOIN countries co ON co.iso_code = v.iso_code;

INSERT INTO discounts_brands (
    carrier_brand_id, country_id, percentage, valid_from, valid_until
)
SELECT cb.id, co.id, v.percentage, CURRENT_DATE - v.from_offset, CURRENT_DATE + v.until_offset
FROM (
    VALUES
        ('Iberia Supply', 'Aether', 'FR', 4.50::DECIMAL(5,2), 20, 10),
        ('Hexa Distribution', 'Velora', 'ES', 3.00::DECIMAL(5,2), 8, 50)
) AS v(carrier_name, brand_name, iso_code, percentage, from_offset, until_offset)
JOIN carriers c ON c.name = v.carrier_name
JOIN brands b ON b.name = v.brand_name
JOIN carrier_brands cb ON cb.carrier_id = c.id AND cb.brand_id = b.id
JOIN countries co ON co.iso_code = v.iso_code;

-- 9. Plazos
INSERT INTO delivery_times_products (
    carrier_country_id, product_id, days, calc_type
)
SELECT cc.id, p.id, 2, 'fixed'
FROM carrier_countries cc
JOIN carriers c ON c.id = cc.carrier_id
JOIN countries co ON co.id = cc.country_id
JOIN products p ON p.carrier_id = c.id AND p.reference = 'AE-LAMP-001'
WHERE c.name = 'Iberia Supply' AND co.iso_code = 'ES';

INSERT INTO delivery_times_groups (
    group_id, country_id, days, calc_type
)
SELECT pg.id, co.id, v.days, v.calc_type
FROM (
    VALUES
        ('Furniture core', 'ES', 1, 'add'),
        ('Premium outdoor', 'FR', 4, 'fixed')
) AS v(group_name, iso_code, days, calc_type)
JOIN product_groups pg ON pg.name = v.group_name
JOIN countries co ON co.iso_code = v.iso_code;

INSERT INTO delivery_times_brands (
    carrier_brand_id, country_id, days, calc_type
)
SELECT cb.id, co.id, v.days, v.calc_type
FROM (
    VALUES
        ('Iberia Supply', 'Aether', 'FR', 2, 'add'),
        ('Hexa Distribution', 'Velora', 'ES', 5, 'fixed')
) AS v(carrier_name, brand_name, iso_code, days, calc_type)
JOIN carriers c ON c.name = v.carrier_name
JOIN brands b ON b.name = v.brand_name
JOIN carrier_brands cb ON cb.carrier_id = c.id AND cb.brand_id = b.id
JOIN countries co ON co.iso_code = v.iso_code;

-- 10. Pendientes
INSERT INTO pending_products (
    carrier_id, country_id, reference, ean_13, name,
    brand_name_raw, category_name_raw, suggested_base_price, status, created_at
)
SELECT c.id, co.id, v.reference, v.ean_13, v.product_name,
       v.brand_name_raw, v.category_name_raw, v.suggested_base_price, v.status, v.created_at
FROM (
    VALUES
        ('Iberia Supply', 'ES', 'RAW-ES-900', '7501234567800', 'Wall Sconce Prototype', 'Aether', 'Lighting', 89.00::DECIMAL(12,2), 'pending', NOW() - INTERVAL '3 days'),
        ('Hexa Distribution', 'FR', 'RAW-FR-122', NULL, 'Garden Bench Limited', 'Velora', 'Outdoor', 310.00::DECIMAL(12,2), 'approved', NOW() - INTERVAL '12 days')
) AS v(carrier_name, iso_code, reference, ean_13, product_name, brand_name_raw, category_name_raw, suggested_base_price, status, created_at)
JOIN carriers c ON c.name = v.carrier_name
JOIN countries co ON co.iso_code = v.iso_code;

-- 11. Clientes y ventas
INSERT INTO customers (name, email, country_id, created_at)
SELECT v.customer_name, v.email, co.id, v.created_at
FROM (
    VALUES
        ('Casa Nube Studio', 'purchasing@casanube.test', 'ES', NOW() - INTERVAL '14 days'),
        ('Maison Delta', 'ops@maisondelta.test', 'FR', NOW() - INTERVAL '9 days'),
        ('Loja Aurora', 'compras@aurora.test', 'PT', NOW() - INTERVAL '5 days')
) AS v(customer_name, email, iso_code, created_at)
JOIN countries co ON co.iso_code = v.iso_code;

INSERT INTO sales (customer_id, carrier_id, country_id, sale_date, total_amount, status)
SELECT cu.id, c.id, co.id, v.sale_date, v.total_amount, v.status
FROM (
    VALUES
        ('Casa Nube Studio', 'Iberia Supply', 'ES', NOW() - INTERVAL '7 days', 225.60::DECIMAL(12,2), 'completed'),
        ('Maison Delta', 'Hexa Distribution', 'FR', NOW() - INTERVAL '4 days', 182.28::DECIMAL(12,2), 'completed'),
        ('Loja Aurora', 'Iberia Supply', 'PT', NOW() - INTERVAL '2 days', 169.20::DECIMAL(12,2), 'pending')
) AS v(customer_name, carrier_name, iso_code, sale_date, total_amount, status)
JOIN customers cu ON cu.name = v.customer_name
JOIN carriers c ON c.name = v.carrier_name
JOIN countries co ON co.iso_code = v.iso_code;

INSERT INTO sale_items (
    sale_id, product_id, quantity, unit_price,
    discount_applied, final_unit_price, total_line
)
SELECT s.id, p.id, v.quantity, v.unit_price, v.discount_applied, v.final_unit_price, v.total_line
FROM (
    VALUES
        ('Casa Nube Studio', 'Iberia Supply', 'ND-CHAIR-002', 1, 240.00::DECIMAL(12,2), 6.00::DECIMAL(5,2), 225.60::DECIMAL(12,2), 225.60::DECIMAL(12,2)),
        ('Maison Delta', 'Hexa Distribution', 'AE-OUT-004', 2, 98.00::DECIMAL(12,2), 7.00::DECIMAL(5,2), 91.14::DECIMAL(12,2), 182.28::DECIMAL(12,2)),
        ('Loja Aurora', 'Iberia Supply', 'VL-TABLE-003', 1, 180.00::DECIMAL(12,2), 6.00::DECIMAL(5,2), 169.20::DECIMAL(12,2), 169.20::DECIMAL(12,2))
) AS v(customer_name, carrier_name, reference, quantity, unit_price, discount_applied, final_unit_price, total_line)
JOIN customers cu ON cu.name = v.customer_name
JOIN carriers c ON c.name = v.carrier_name
JOIN sales s ON s.customer_id = cu.id AND s.carrier_id = c.id
JOIN products p ON p.reference = v.reference;

-- 12. Auditoria de precios
INSERT INTO price_audit (
    product_price_id, field_changed, old_value, new_value, changed_at
)
SELECT pp.id, v.field_changed, v.old_value, v.new_value, v.changed_at
FROM (
    VALUES
        ('Iberia Supply', 'AE-LAMP-001', 'ES', 'final_price', 120.00::DECIMAL(12,2), 114.00::DECIMAL(12,2), NOW() - INTERVAL '2 days'),
        ('Hexa Distribution', 'AE-OUT-004', 'FR', 'base_price', 95.00::DECIMAL(12,2), 98.00::DECIMAL(12,2), NOW() - INTERVAL '3 days')
) AS v(carrier_name, reference, iso_code, field_changed, old_value, new_value, changed_at)
JOIN carriers c ON c.name = v.carrier_name
JOIN products p ON p.reference = v.reference AND p.carrier_id = c.id
JOIN countries co ON co.iso_code = v.iso_code
JOIN product_prices pp ON pp.carrier_id = c.id AND pp.product_id = p.id AND pp.country_id = co.id;
