SELECT schemaname, relname, indexname, indexdef
FROM pg_indexes
WHERE schemaname = 'public'
ORDER BY relname, indexname
