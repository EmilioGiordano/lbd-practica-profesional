-- ============================================================
-- TRUNCATE ALL TABLES IN retail SCHEMA
-- Order: Most dependent tables first (respecting FK constraints)
-- ============================================================

SET search_path TO retail;

-- Tables with no dependencies (safe to truncate first)
TRUNCATE TABLE price_audit CASCADE;
TRUNCATE TABLE sale_items CASCADE;
TRUNCATE TABLE sales CASCADE;
TRUNCATE TABLE product_group_assignments CASCADE;
TRUNCATE TABLE discounts_products CASCADE;
TRUNCATE TABLE discounts_groups CASCADE;
TRUNCATE TABLE discounts_brands CASCADE;
TRUNCATE TABLE delivery_times_products CASCADE;
TRUNCATE TABLE delivery_times_groups CASCADE;
TRUNCATE TABLE delivery_times_brands CASCADE;

-- Tables with FK to brands, categories, series, materials, carriers, countries
TRUNCATE TABLE pending_products CASCADE;
TRUNCATE TABLE product_prices CASCADE;
TRUNCATE TABLE products CASCADE;
TRUNCATE TABLE product_groups CASCADE;
TRUNCATE TABLE carrier_brands CASCADE;
TRUNCATE TABLE carrier_countries CASCADE;
TRUNCATE TABLE customers CASCADE;

-- Base tables (no foreign keys)
TRUNCATE TABLE carriers CASCADE;
TRUNCATE TABLE countries CASCADE;
TRUNCATE TABLE brands CASCADE;
TRUNCATE TABLE categories CASCADE;
TRUNCATE TABLE series CASCADE;
TRUNCATE TABLE materials CASCADE;

-- Reset sequences to start from 1
ALTER SEQUENCE countries_id_seq RESTART WITH 1;
ALTER SEQUENCE brands_id_seq RESTART WITH 1;
ALTER SEQUENCE categories_id_seq RESTART WITH 1;
ALTER SEQUENCE series_id_seq RESTART WITH 1;
ALTER SEQUENCE materials_id_seq RESTART WITH 1;
ALTER SEQUENCE carriers_id_seq RESTART WITH 1;
ALTER SEQUENCE carrier_countries_id_seq RESTART WITH 1;
ALTER SEQUENCE carrier_brands_id_seq RESTART WITH 1;
ALTER SEQUENCE products_id_seq RESTART WITH 1;
ALTER SEQUENCE product_groups_id_seq RESTART WITH 1;
ALTER SEQUENCE product_group_assignments_id_seq RESTART WITH 1;
ALTER SEQUENCE product_prices_id_seq RESTART WITH 1;
ALTER SEQUENCE discounts_products_id_seq RESTART WITH 1;
ALTER SEQUENCE discounts_groups_id_seq RESTART WITH 1;
ALTER SEQUENCE discounts_brands_id_seq RESTART WITH 1;
ALTER SEQUENCE delivery_times_products_id_seq RESTART WITH 1;
ALTER SEQUENCE delivery_times_groups_id_seq RESTART WITH 1;
ALTER SEQUENCE delivery_times_brands_id_seq RESTART WITH 1;
ALTER SEQUENCE pending_products_id_seq RESTART WITH 1;
ALTER SEQUENCE customers_id_seq RESTART WITH 1;
ALTER SEQUENCE sales_id_seq RESTART WITH 1;
ALTER SEQUENCE sale_items_id_seq RESTART WITH 1;
ALTER SEQUENCE price_audit_id_seq RESTART WITH 1;
