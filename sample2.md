select history.id, history.customer_name, sum(history.subtotal) as 
total, history.status, history.created_at
from (select purchases.id as id, item_purchase.id as pivot_id,
customers.name as customer_name,
items.price * item_purchase.quantity as subtotal,
items.name as item_name, items.price as item_price,
item_purchase.quantity, purchases.status, purchases.created_at,
purchases.updated_at 
from purchases
left join item_purchase on purchases.id = item_purchase.purchase_id
left join items on item_purchase.item_id = items.id 
left join customers on purchases.customer_id = customers.id 
) as history
group by history.id