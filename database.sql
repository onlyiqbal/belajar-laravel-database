create table categories(
  id varchar(100) not null primary key,
  name varchar(100) not null,
  description text,
  created_at timestamp
);

create table counters(
  id varchar(100) not null primary key,
  counter int default 0
);

insert into counter(id, counter) values ('sample', 0);

create table products
(
    id          varchar(100) not null primary key,
    name        varchar(100) not null,
    description text         null,
    price       int          not null,
    category_id varchar(100) not null,
    created_at  timestamp    not null default current_timestamp,
    constraint fk_category_id foreign key (category_id) references categories (id)
);

DROP TABLE categories;
DROP TABLE products;
DROP TABLE counters;