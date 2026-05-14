CREATE TABLE server (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    hostname VARCHAR(255) NOT NULL,
    os_family VARCHAR(50) NOT NULL,
    status VARCHAR(20) DEFAULT 'offline',
    last_seen TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE firewall_rule (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    action VARCHAR(10) DEFAULT 'allow',
    direction VARCHAR(10) DEFAULT 'inbound',
    protocol VARCHAR(10) DEFAULT 'tcp',
    dst_port INTEGER,
    src_ip VARCHAR(50) DEFAULT '0.0.0.0/0',
    status VARCHAR(20) DEFAULT 'pending'
);

CREATE TABLE audit_logs (
    id SERIAL PRIMARY KEY,
    timestamp TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    action VARCHAR(100),
    target_uuid UUID,
    details JSONB
);
