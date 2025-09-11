INSERT INTO products (name, model, brand_id, category_id, price, description, horsepower, stroke, stock_quantity, sku, status, featured) 
VALUES ('Marine Propeller', 'MP-15', 1, (SELECT id FROM categories WHERE name = 'Accessories'), 149.99, 'Durable 3-blade aluminum propeller for mid-range motors.', 0, '4-stroke', 50, 'YAM-PROP-15', 'active', 0);
