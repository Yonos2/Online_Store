CREATE TABLE IF NOT EXISTS public.customer_product_notification_dismissals (
    customer_id BIGINT NOT NULL
        REFERENCES public.users (id) ON DELETE CASCADE,
    notification_id BIGINT NOT NULL
        REFERENCES public.product_notifications (id) ON DELETE CASCADE,
    dismissed_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (customer_id, notification_id)
);
