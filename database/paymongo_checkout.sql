ALTER TABLE public.orders
    DROP CONSTRAINT IF EXISTS orders_payment_method_check;

ALTER TABLE public.orders
    ADD CONSTRAINT orders_payment_method_check
        CHECK (payment_method IN ('cod', 'manual', 'paymongo'));

ALTER TABLE public.orders
    ADD COLUMN IF NOT EXISTS paymongo_checkout_session_id VARCHAR(100),
    ADD COLUMN IF NOT EXISTS paymongo_payment_id VARCHAR(100);

CREATE UNIQUE INDEX IF NOT EXISTS orders_paymongo_checkout_session_id_idx
    ON public.orders (paymongo_checkout_session_id)
    WHERE paymongo_checkout_session_id IS NOT NULL;
