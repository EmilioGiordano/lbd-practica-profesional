-- ============================================================
-- LAB BD - SPRINT INTEGRADOR
-- Esquema reducido: Catálogo + Ventas con jerarquía de descuentos/plazos
-- Motor: PostgreSQL 14+
-- NOTA: Este DDL NO incluye índices de optimización (salvo PK/FK/UNIQUE).
--       Los alumnos deben proponerlos y justificarlos.
-- ============================================================

DROP SCHEMA IF EXISTS retail CASCADE;
CREATE SCHEMA retail;
SET search_path TO retail;

-- ============================================================
-- 1. CATÁLOGO MAESTRO
-- ============================================================

CREATE TABLE IF NOT EXISTS countries (
    id          BIGSERIAL PRIMARY KEY,
    name        VARCHAR(100) NOT NULL,
    iso_code    VARCHAR(2) NOT NULL UNIQUE,
    currency    VARCHAR(3) DEFAULT 'EUR'
);

CREATE TABLE IF NOT EXISTS brands (
    id          BIGSERIAL PRIMARY KEY,
    name        VARCHAR(100) NOT NULL,
    active      BOOLEAN NOT NULL DEFAULT TRUE
);

CREATE TABLE IF NOT EXISTS categories (
    id          BIGSERIAL PRIMARY KEY,
    name        VARCHAR(100) NOT NULL
);

CREATE TABLE IF NOT EXISTS series (
    id          BIGSERIAL PRIMARY KEY,
    name        VARCHAR(100) NOT NULL
);

CREATE TABLE IF NOT EXISTS materials (
    id          BIGSERIAL PRIMARY KEY,
    name        VARCHAR(100) NOT NULL
);

-- ============================================================
-- 2. VENDEDORES (CARRIERS)
-- ============================================================

CREATE TABLE IF NOT EXISTS carriers (
    id              BIGSERIAL PRIMARY KEY,
    name            VARCHAR(150) NOT NULL,
    email           VARCHAR(100),
    phone           VARCHAR(50),
    active          BOOLEAN NOT NULL DEFAULT TRUE,
    observations    TEXT
);

-- ============================================================
-- 3. CONFIGURACIÓN CARRIER × PAÍS (NIVEL BASE)
-- ============================================================
-- Aquí vive el descuento y plazo BASE (prioridad 4, la más baja).

CREATE TABLE IF NOT EXISTS carrier_countries (
    id                  BIGSERIAL PRIMARY KEY,
    carrier_id          BIGINT NOT NULL REFERENCES carriers(id),
    country_id          BIGINT NOT NULL REFERENCES countries(id),
    base_discount_rate  DECIMAL(5,2) CHECK (base_discount_rate BETWEEN 0 AND 100),
    base_delivery_days  INTEGER CHECK (base_delivery_days > 0),
    active              BOOLEAN NOT NULL DEFAULT TRUE,
    UNIQUE (carrier_id, country_id)
);

-- ============================================================
-- 4. RELACIÓN CARRIER × MARCA
-- ============================================================
-- Necesaria para aplicar descuentos/plazos a nivel marca.

CREATE TABLE IF NOT EXISTS carrier_brands (
    id          BIGSERIAL PRIMARY KEY,
    carrier_id  BIGINT NOT NULL REFERENCES carriers(id),
    brand_id    BIGINT NOT NULL REFERENCES brands(id),
    active      BOOLEAN NOT NULL DEFAULT TRUE,
    UNIQUE (carrier_id, brand_id)
);

-- ============================================================
-- 5. PRODUCTOS
-- ============================================================
-- Identidad por carrier: un mismo carrier no puede repetir reference.
-- Distintos carriers SÍ pueden compartir la misma reference.

CREATE TABLE IF NOT EXISTS products (
    id              BIGSERIAL PRIMARY KEY,
    carrier_id      BIGINT NOT NULL REFERENCES carriers(id),
    reference       VARCHAR(50) NOT NULL,
    ean_13          VARCHAR(13),
    name            VARCHAR(200) NOT NULL,
    description     TEXT,
    brand_id        BIGINT NOT NULL REFERENCES brands(id),
    category_id     BIGINT NOT NULL REFERENCES categories(id),
    series_id       BIGINT NOT NULL REFERENCES series(id),
    material_id     BIGINT NOT NULL REFERENCES materials(id),
    weight_kg       DECIMAL(10,3),
    volume_m3       DECIMAL(10,3),
    active          BOOLEAN NOT NULL DEFAULT TRUE,
    created_at      TIMESTAMP NOT NULL DEFAULT NOW(),
    UNIQUE (carrier_id, reference)
);

-- ============================================================
-- 6. GRUPOS DE PRODUCTOS (por carrier)
-- ============================================================

CREATE TABLE IF NOT EXISTS product_groups (
    id          BIGSERIAL PRIMARY KEY,
    carrier_id  BIGINT NOT NULL REFERENCES carriers(id),
    name        VARCHAR(100) NOT NULL
);

CREATE TABLE IF NOT EXISTS product_group_assignments (
    id          BIGSERIAL PRIMARY KEY,
    product_id  BIGINT NOT NULL REFERENCES products(id),
    group_id    BIGINT NOT NULL REFERENCES product_groups(id),
    UNIQUE (product_id, group_id)
);

-- ============================================================
-- 7. PRECIOS DE VENTA ACTIVOS (catálogo operativo)
-- ============================================================
-- Un producto tiene un precio por carrier + país.
-- El precio final se calcula aplicando la jerarquía de descuentos.

CREATE TABLE IF NOT EXISTS product_prices (
    id              BIGSERIAL PRIMARY KEY,
    carrier_id      BIGINT NOT NULL REFERENCES carriers(id),
    product_id      BIGINT NOT NULL REFERENCES products(id),
    country_id      BIGINT NOT NULL REFERENCES countries(id),
    base_price      DECIMAL(12,2) NOT NULL CHECK (base_price >= 0),
    final_price     DECIMAL(12,2) NOT NULL CHECK (final_price >= 0),
    stock_quantity  INTEGER NOT NULL DEFAULT 0 CHECK (stock_quantity >= 0),
    delivery_days   INTEGER CHECK (delivery_days > 0),
    status          VARCHAR(20) NOT NULL DEFAULT 'active',
    updated_at      TIMESTAMP NOT NULL DEFAULT NOW(),
    UNIQUE (carrier_id, product_id, country_id)
);

-- ============================================================
-- 8. JERARQUÍA DE DESCUENTOS
-- ============================================================
-- Prioridad (de mayor a menor):
--   1. Producto  -> discounts_products
--   2. Grupo     -> discounts_groups
--   3. Marca     -> discounts_brands
--   4. Base      -> carrier_countries.base_discount_rate
--
-- Solo se aplica UN nivel: el de mayor prioridad disponible.
-- Las fechas permiten descuentos temporales (vigencia).

CREATE TABLE IF NOT EXISTS discounts_products (
    id              BIGSERIAL PRIMARY KEY,
    carrier_id      BIGINT NOT NULL REFERENCES carriers(id),
    product_id      BIGINT NOT NULL REFERENCES products(id),
    country_id      BIGINT NOT NULL REFERENCES countries(id),
    percentage      DECIMAL(5,2) NOT NULL CHECK (percentage BETWEEN 0 AND 100),
    valid_from      DATE NOT NULL,
    valid_until     DATE NOT NULL,
    CHECK (valid_until >= valid_from),
    UNIQUE (carrier_id, product_id, country_id)
);

CREATE TABLE IF NOT EXISTS discounts_groups (
    id              BIGSERIAL PRIMARY KEY,
    group_id        BIGINT NOT NULL REFERENCES product_groups(id),
    country_id      BIGINT NOT NULL REFERENCES countries(id),
    percentage      DECIMAL(5,2) NOT NULL CHECK (percentage BETWEEN 0 AND 100),
    valid_from      DATE NOT NULL,
    valid_until     DATE NOT NULL,
    CHECK (valid_until >= valid_from),
    UNIQUE (group_id, country_id)
    -- carrier_id es implícito via product_groups.carrier_id
);

CREATE TABLE IF NOT EXISTS discounts_brands (
    id              BIGSERIAL PRIMARY KEY,
    carrier_brand_id BIGINT NOT NULL REFERENCES carrier_brands(id),
    country_id      BIGINT NOT NULL REFERENCES countries(id),
    percentage      DECIMAL(5,2) NOT NULL CHECK (percentage BETWEEN 0 AND 100),
    valid_from      DATE NOT NULL,
    valid_until     DATE NOT NULL,
    CHECK (valid_until >= valid_from),
    UNIQUE (carrier_brand_id, country_id)
    -- carrier_id es implícito via carrier_brands.carrier_id
);

-- ============================================================
-- 9. JERARQUÍA DE PLAZOS DE ENTREGA
-- ============================================================
-- Prioridad idéntica a descuentos:
--   1. Producto  -> delivery_times_products
--   2. Grupo     -> delivery_times_groups
--   3. Marca     -> delivery_times_brands
--   4. Base      -> carrier_countries.base_delivery_days
--
-- calc_type: 'fixed' reemplaza el base, 'add' suma días al base.

CREATE TABLE IF NOT EXISTS delivery_times_products (
    id                  BIGSERIAL PRIMARY KEY,
    carrier_country_id  BIGINT NOT NULL REFERENCES carrier_countries(id),
    product_id          BIGINT NOT NULL REFERENCES products(id),
    days                INTEGER NOT NULL CHECK (days > 0),
    calc_type           VARCHAR(10) NOT NULL DEFAULT 'fixed' CHECK (calc_type IN ('fixed','add')),
    UNIQUE (carrier_country_id, product_id)
);

CREATE TABLE IF NOT EXISTS delivery_times_groups (
    id          BIGSERIAL PRIMARY KEY,
    group_id    BIGINT NOT NULL REFERENCES product_groups(id),
    country_id  BIGINT NOT NULL REFERENCES countries(id),
    days        INTEGER NOT NULL CHECK (days > 0),
    calc_type   VARCHAR(10) NOT NULL DEFAULT 'fixed' CHECK (calc_type IN ('fixed','add')),
    UNIQUE (group_id, country_id)
);

CREATE TABLE IF NOT EXISTS delivery_times_brands (
    id                  BIGSERIAL PRIMARY KEY,
    carrier_brand_id    BIGINT NOT NULL REFERENCES carrier_brands(id),
    country_id          BIGINT NOT NULL REFERENCES countries(id),
    days                INTEGER NOT NULL CHECK (days > 0),
    calc_type           VARCHAR(10) NOT NULL DEFAULT 'fixed' CHECK (calc_type IN ('fixed','add')),
    UNIQUE (carrier_brand_id, country_id)
);

-- ============================================================
-- 10. PRODUCTOS PENDIENTES
-- ============================================================
-- Cuando un carrier ofrece productos de una marca que aún no
-- están asociados a su catálogo formal, quedan aquí para revisión.

CREATE TABLE IF NOT EXISTS pending_products (
    id                  BIGSERIAL PRIMARY KEY,
    carrier_id          BIGINT NOT NULL REFERENCES carriers(id),
    country_id          BIGINT NOT NULL REFERENCES countries(id),
    reference           VARCHAR(50) NOT NULL,
    ean_13              VARCHAR(13),
    name                VARCHAR(200),
    brand_name_raw      VARCHAR(100) NOT NULL,   -- nombre leído del feed
    category_name_raw   VARCHAR(100),
    suggested_base_price DECIMAL(12,2) CHECK (suggested_base_price >= 0),
    status              VARCHAR(20) NOT NULL DEFAULT 'pending' CHECK (status IN ('pending','approved','rejected')),
    created_at          TIMESTAMP NOT NULL DEFAULT NOW()
);

-- ============================================================
-- 11. VENTAS (nuevo dominio)
-- ============================================================

CREATE TABLE IF NOT EXISTS customers (
    id          BIGSERIAL PRIMARY KEY,
    name        VARCHAR(150) NOT NULL,
    email       VARCHAR(100),
    country_id  BIGINT REFERENCES countries(id),
    created_at  TIMESTAMP NOT NULL DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS sales (
    id              BIGSERIAL PRIMARY KEY,
    customer_id     BIGINT NOT NULL REFERENCES customers(id),
    carrier_id      BIGINT NOT NULL REFERENCES carriers(id),
    country_id      BIGINT NOT NULL REFERENCES countries(id),
    sale_date       TIMESTAMP NOT NULL DEFAULT NOW(),
    total_amount    DECIMAL(12,2) NOT NULL DEFAULT 0 CHECK (total_amount >= 0),
    status          VARCHAR(20) NOT NULL DEFAULT 'completed' CHECK (status IN ('pending','completed','cancelled'))
);

CREATE TABLE IF NOT EXISTS sale_items (
    id                  BIGSERIAL PRIMARY KEY,
    sale_id             BIGINT NOT NULL REFERENCES sales(id),
    product_id          BIGINT NOT NULL REFERENCES products(id),
    quantity            INTEGER NOT NULL CHECK (quantity > 0),
    unit_price          DECIMAL(12,2) NOT NULL CHECK (unit_price >= 0),
    discount_applied    DECIMAL(5,2) NOT NULL DEFAULT 0 CHECK (discount_applied BETWEEN 0 AND 100),
    final_unit_price    DECIMAL(12,2) NOT NULL CHECK (final_unit_price >= 0),
    total_line          DECIMAL(12,2) NOT NULL CHECK (total_line >= 0)
);

-- ============================================================
-- 12. AUDITORÍA DE PRECIOS (trigger-ready)
-- ============================================================
-- Los alumnos deben crear el trigger que alimente esta tabla
-- cuando cambia product_prices.base_price o final_price.

CREATE TABLE IF NOT EXISTS price_audit (
    id              BIGSERIAL PRIMARY KEY,
    product_price_id BIGINT NOT NULL,
    field_changed   VARCHAR(20) NOT NULL CHECK (field_changed IN ('base_price','final_price')),
    old_value       DECIMAL(12,2) NOT NULL,
    new_value       DECIMAL(12,2) NOT NULL,
    changed_at      TIMESTAMP NOT NULL DEFAULT NOW()
);

-- ============================================================
-- COMENTARIOS DE NEGOCIO (para que lean en pgAdmin / DataGrip)
-- ============================================================

COMMENT ON TABLE discounts_products IS 'Prioridad 1 (máxima) en jerarquía de descuentos. Si existe un registro vigente aquí, ignora grupo, marca y base.';
COMMENT ON TABLE discounts_groups IS 'Prioridad 2. Solo se evalúa si NO hay descuento a nivel producto. El carrier está implícito vía product_groups.carrier_id.';
COMMENT ON TABLE discounts_brands IS 'Prioridad 3. Solo se evalúa si NO hay descuento a nivel producto ni grupo. El carrier está implícito vía carrier_brands.';
COMMENT ON TABLE carrier_countries IS 'Prioridad 4 (base). Descuento y plazo por defecto cuando no hay configuración específica de producto, grupo o marca.';

COMMENT ON TABLE delivery_times_products IS 'Prioridad 1 (máxima) en jerarquía de plazos. Si existe, ignora grupo, marca y base.';
COMMENT ON TABLE delivery_times_groups IS 'Prioridad 2 en plazos. Solo si no hay plazo de producto. Carrier implícito vía product_groups.';
COMMENT ON TABLE delivery_times_brands IS 'Prioridad 3 en plazos. Solo si no hay plazo de producto ni grupo. Carrier implícito vía carrier_brands.';

COMMENT ON TABLE product_prices IS 'Catálogo operativo: precio final por carrier+producto+país. El final_price DEBE reflejar el descuento aplicado según jerarquía vigente.';
COMMENT ON TABLE pending_products IS 'Productos detectados en feeds que aún no están en el catálogo formal del carrier. Requieren revisión manual para alta.';
