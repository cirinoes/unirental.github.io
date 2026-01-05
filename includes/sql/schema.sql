CREATE TABLE IF NOT EXISTS users (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  email TEXT NOT NULL UNIQUE,
  full_name TEXT NOT NULL DEFAULT '',
  password_hash TEXT NOT NULL,
  role TEXT NOT NULL DEFAULT 'member', -- member/admin
  created_at TEXT NOT NULL
);

CREATE TABLE IF NOT EXISTS items (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  name TEXT NOT NULL,
  category TEXT NOT NULL DEFAULT 'General',
  sku TEXT NOT NULL UNIQUE,            -- your plate_number equivalent
  daily_rate REAL NOT NULL DEFAULT 0.0,
  image_filename TEXT                  -- e.g. ABC123.jpg
);

CREATE TABLE IF NOT EXISTS rentals (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  user_id INTEGER NOT NULL,
  item_id INTEGER NOT NULL,
  start_date TEXT NOT NULL,            -- YYYY-MM-DD
  end_date TEXT NOT NULL,              -- YYYY-MM-DD
  cost REAL NOT NULL,
  status TEXT NOT NULL DEFAULT 'Paid',
  created_at TEXT NOT NULL,
  FOREIGN KEY(user_id) REFERENCES users(id),
  FOREIGN KEY(item_id) REFERENCES items(id)
);

CREATE TABLE IF NOT EXISTS transactions (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  user_id INTEGER NOT NULL,
  created_at TEXT NOT NULL,            -- YYYY-MM-DD HH:MM:SS
  status TEXT NOT NULL DEFAULT 'PAID',
  payment_method TEXT NOT NULL DEFAULT 'DUMMY',
  payment_ref TEXT NOT NULL,
  total_amount REAL NOT NULL,
  email_sent INTEGER NOT NULL DEFAULT 0,
  FOREIGN KEY(user_id) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS transaction_items (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  transaction_id INTEGER NOT NULL,
  item_id INTEGER NOT NULL,
  unit_price REAL NOT NULL,
  quantity INTEGER NOT NULL,
  subtotal REAL NOT NULL,
  start_date TEXT,
  end_date TEXT,
  FOREIGN KEY(transaction_id) REFERENCES transactions(id),
  FOREIGN KEY(item_id) REFERENCES items(id)
);