<?php
return [
    "categories"=> [
        "fields"=> [
            "id"=> [
                "description"=> "",
                "name"=> "id",
                "comment"=> "",
                "type"=> [
                    "proto"=> "int",
                    "length"=> "10"
                ],
                "iskey"=> true,
                "required"=> false,
                "default"=> null,
                "referencedBy"=> [
                    [
                        "table"=> "categories",
                        "field"=> "categories_id"
                    ]
                ]
            ],
            "name"=> [
                "description"=> "",
                "name"=> "name",
                "comment"=> "",
                "type"=> [
                    "proto"=> "varchar",
                    "length"=> "45"
                ],
                "iskey"=> false,
                "required"=> true,
                "default"=> null
            ],
            "categories_id"=> [
                "description"=> "",
                "name"=> "categories_id",
                "comment"=> "",
                "type"=> [
                    "proto"=> "int",
                    "length"=> "10"
                ],
                "iskey"=> false,
                "required"=> false,
                "default"=> null,
                "foreignKey"=> [
                    "table"=> "categories",
                    "field"=> "id"
                ]
            ]
        ],
        "name"=> "categories",
        "description"=> "",
        "comment"=> "",
        "type"=> "table",
        "keyFld"=> "id",
        "relations"=> [
            "categories_id"=> [
                "table"=> "categories",
                "field"=> "id",
                "type"=> "outbound",
                "fkfield"=> "categories_id"
            ],
            "categories"=> [
                "table"=> "categories",
                "field"=> "categories_id",
                "type"=> "inbound"
            ]
        ]
    ],
    "config_cont_conta"=> [
        "fields"=> [
            "name"=> [
                "description"=> "",
                "name"=> "name",
                "comment"=> "",
                "type"=> [
                    "proto"=> "varchar",
                    "length"=> "50"
                ],
                "iskey"=> true,
                "required"=> true,
                "default"=> null
            ],
            "cont"=> [
                "description"=> "",
                "name"=> "cont",
                "comment"=> "",
                "type"=> [
                    "proto"=> "varchar",
                    "length"=> "10"
                ],
                "iskey"=> true,
                "required"=> true,
                "default"=> null,
                "referencedBy"=> [
                    [
                        "table"=> "products",
                        "field"=> "cont_conta"
                    ]
                ]
            ]
        ],
        "name"=> "config_cont_conta",
        "description"=> "",
        "comment"=> "",
        "type"=> "table",
        "keyFld"=> "cont",
        "relations"=> [
            "products"=> [
                "table"=> "products",
                "field"=> "cont_conta",
                "type"=> "inbound"
            ]
        ]
    ],
    "config_doc_numbers"=> [
        "fields"=> [
            "doc_type"=> [
                "description"=> "",
                "name"=> "doc_type",
                "comment"=> "",
                "type"=> [
                    "proto"=> "varchar",
                    "length"=> "20"
                ],
                "iskey"=> true,
                "required"=> true,
                "default"=> null
            ],
            "serie"=> [
                "description"=> "",
                "name"=> "serie",
                "comment"=> "",
                "type"=> [
                    "proto"=> "varchar",
                    "length"=> "10"
                ],
                "iskey"=> true,
                "required"=> true,
                "default"=> ""
            ],
            "current_no"=> [
                "description"=> "",
                "name"=> "current_no",
                "comment"=> "",
                "type"=> [
                    "proto"=> "int",
                    "length"=> "10"
                ],
                "iskey"=> false,
                "required"=> true,
                "default"=> null
            ],
            "valid_from"=> [
                "description"=> "",
                "name"=> "valid_from",
                "comment"=> "",
                "type"=> [
                    "proto"=> "timestamp"
                ],
                "iskey"=> true,
                "required"=> false,
                "default"=> "CURRENT_TIMESTAMP"
            ],
            "valid_to"=> [
                "description"=> "",
                "name"=> "valid_to",
                "comment"=> "",
                "type"=> [
                    "proto"=> "timestamp"
                ],
                "iskey"=> false,
                "required"=> false,
                "default"=> "2038-01-01 00=>00=>00"
            ]
        ],
        "name"=> "config_doc_numbers",
        "description"=> "",
        "comment"=> "",
        "type"=> "table",
        "keyFld"=> "valid_from"
    ],
    "config_invoice_series"=> [
        "fields"=> [
            "id"=> [
                "description"=> "",
                "name"=> "id",
                "comment"=> "",
                "type"=> [
                    "proto"=> "int",
                    "length"=> "10"
                ],
                "iskey"=> true,
                "required"=> false,
                "default"=> null
            ],
            "serie"=> [
                "description"=> "",
                "name"=> "serie",
                "comment"=> "",
                "type"=> [
                    "proto"=> "varchar",
                    "length"=> "45"
                ],
                "iskey"=> false,
                "required"=> false,
                "default"=> null
            ],
            "range_min"=> [
                "description"=> "",
                "name"=> "range_min",
                "comment"=> "",
                "type"=> [
                    "proto"=> "varchar",
                    "length"=> "45"
                ],
                "iskey"=> false,
                "required"=> false,
                "default"=> null
            ],
            "range_max"=> [
                "description"=> "",
                "name"=> "range_max",
                "comment"=> "",
                "type"=> [
                    "proto"=> "varchar",
                    "length"=> "45"
                ],
                "iskey"=> false,
                "required"=> false,
                "default"=> null
            ],
            "current"=> [
                "description"=> "",
                "name"=> "current",
                "comment"=> "",
                "type"=> [
                    "proto"=> "varchar",
                    "length"=> "45"
                ],
                "iskey"=> false,
                "required"=> false,
                "default"=> null
            ],
            "active"=> [
                "description"=> "",
                "name"=> "active",
                "comment"=> "",
                "type"=> [
                    "proto"=> "varchar",
                    "length"=> "45"
                ],
                "iskey"=> false,
                "required"=> false,
                "default"=> null
            ]
        ],
        "name"=> "config_invoice_series",
        "description"=> "",
        "comment"=> "",
        "type"=> "table",
        "keyFld"=> "id"
    ],
    "config_units"=> [
        "fields"=> [
            "unit"=> [
                "description"=> "",
                "name"=> "unit",
                "comment"=> "",
                "type"=> [
                    "proto"=> "varchar",
                    "length"=> "20"
                ],
                "iskey"=> true,
                "required"=> true,
                "default"=> null,
                "referencedBy"=> [
                    [
                        "table"=> "inventory",
                        "field"=> "unit"
                    ],
                    [
                        "table"=> "invoice_items",
                        "field"=> "unit"
                    ]
                ]
            ]
        ],
        "name"=> "config_units",
        "description"=> "",
        "comment"=> "",
        "type"=> "table",
        "keyFld"=> "unit",
        "relations"=> [
            "inventory"=> [
                "table"=> "inventory",
                "field"=> "unit",
                "type"=> "inbound"
            ],
            "invoice_items"=> [
                "table"=> "invoice_items",
                "field"=> "unit",
                "type"=> "inbound"
            ]
        ]
    ],
    "inventory"=> [
        "fields"=> [
            "id"=> [
                "description"=> "",
                "name"=> "id",
                "comment"=> "",
                "type"=> [
                    "proto"=> "int",
                    "length"=> "10"
                ],
                "iskey"=> true,
                "required"=> false,
                "default"=> null,
                "referencedBy"=> [
                    [
                        "table"=> "inventory_logs",
                        "field"=> "inventory_id"
                    ],
                    [
                        "table"=> "orders_items_subitems",
                        "field"=> "inventory_id"
                    ],
                    [
                        "table"=> "products_structure",
                        "field"=> "inventory_id"
                    ]
                ]
            ],
            "name"=> [
                "description"=> "",
                "name"=> "name",
                "comment"=> "",
                "type"=> [
                    "proto"=> "varchar",
                    "length"=> "45"
                ],
                "iskey"=> false,
                "required"=> false,
                "default"=> null
            ],
            "vendor"=> [
                "description"=> "",
                "name"=> "vendor",
                "comment"=> "",
                "type"=> [
                    "proto"=> "varchar",
                    "length"=> "45"
                ],
                "iskey"=> false,
                "required"=> false,
                "default"=> null
            ],
            "model"=> [
                "description"=> "",
                "name"=> "model",
                "comment"=> "",
                "type"=> [
                    "proto"=> "varchar",
                    "length"=> "45"
                ],
                "iskey"=> false,
                "required"=> false,
                "default"=> null
            ],
            "is_service"=> [
                "description"=> "",
                "name"=> "is_service",
                "comment"=> "",
                "type"=> [
                    "proto"=> "tinyint",
                    "length"=> "1"
                ],
                "iskey"=> false,
                "required"=> false,
                "default"=> "1"
            ],
            "qty"=> [
                "description"=> "",
                "name"=> "qty",
                "comment"=> "",
                "type"=> [
                    "proto"=> "float"
                ],
                "iskey"=> false,
                "required"=> false,
                "default"=> null
            ],
            "unit"=> [
                "description"=> "",
                "name"=> "unit",
                "comment"=> "",
                "type"=> [
                    "proto"=> "varchar",
                    "length"=> "20"
                ],
                "iskey"=> false,
                "required"=> true,
                "default"=> null,
                "foreignKey"=> [
                    "table"=> "config_units",
                    "field"=> "unit"
                ]
            ],
            "resell_unit_price"=> [
                "description"=> "",
                "name"=> "resell_unit_price",
                "comment"=> "",
                "type"=> [
                    "proto"=> "int",
                    "length"=> "11"
                ],
                "iskey"=> false,
                "required"=> true,
                "default"=> null
            ],
            "comments"=> [
                "description"=> "",
                "name"=> "comments",
                "comment"=> "",
                "type"=> [
                    "proto"=> "varchar",
                    "length"=> "255"
                ],
                "iskey"=> false,
                "required"=> false,
                "default"=> null
            ]
        ],
        "name"=> "inventory",
        "description"=> "",
        "comment"=> "config_units_unit",
        "type"=> "table",
        "keyFld"=> "id",
        "relations"=> [
            "unit"=> [
                "table"=> "config_units",
                "field"=> "unit",
                "type"=> "outbound",
                "fkfield"=> "unit"
            ],
            "inventory_logs"=> [
                "table"=> "inventory_logs",
                "field"=> "inventory_id",
                "type"=> "inbound"
            ],
            "orders_items_subitems"=> [
                "table"=> "orders_items_subitems",
                "field"=> "inventory_id",
                "type"=> "inbound"
            ],
            "products_structure"=> [
                "table"=> "products_structure",
                "field"=> "inventory_id",
                "type"=> "inbound"
            ]
        ]
    ],
    "inventory_logs"=> [
        "fields"=> [
            "id"=> [
                "description"=> "",
                "name"=> "id",
                "comment"=> "",
                "type"=> [
                    "proto"=> "int",
                    "length"=> "10"
                ],
                "iskey"=> true,
                "required"=> false,
                "default"=> null
            ],
            "dt"=> [
                "description"=> "",
                "name"=> "dt",
                "comment"=> "",
                "type"=> [
                    "proto"=> "timestamp"
                ],
                "iskey"=> false,
                "required"=> false,
                "default"=> "CURRENT_TIMESTAMP"
            ],
            "inventory_id"=> [
                "description"=> "",
                "name"=> "inventory_id",
                "comment"=> "",
                "type"=> [
                    "proto"=> "int",
                    "length"=> "10"
                ],
                "iskey"=> false,
                "required"=> true,
                "default"=> null,
                "foreignKey"=> [
                    "table"=> "inventory",
                    "field"=> "id"
                ]
            ],
            "type"=> [
                "description"=> "",
                "name"=> "type",
                "comment"=> "",
                "type"=> [
                    "proto"=> "set",
                    "vals"=> [
                        "in",
                        "out",
                        "correction"
                    ]
                ],
                "iskey"=> false,
                "required"=> false,
                "default"=> ""
            ],
            "qty"=> [
                "description"=> "",
                "name"=> "qty",
                "comment"=> "",
                "type"=> [
                    "proto"=> "float"
                ],
                "iskey"=> false,
                "required"=> true,
                "default"=> null
            ]
        ],
        "name"=> "inventory_logs",
        "description"=> "",
        "comment"=> "",
        "type"=> "table",
        "keyFld"=> "id",
        "relations"=> [
            "inventory_id"=> [
                "table"=> "inventory",
                "field"=> "id",
                "type"=> "outbound",
                "fkfield"=> "inventory_id"
            ]
        ]
    ],
    "invoice_items"=> [
        "fields"=> [
            "id"=> [
                "description"=> "",
                "name"=> "id",
                "comment"=> "",
                "type"=> [
                    "proto"=> "int",
                    "length"=> "10"
                ],
                "iskey"=> true,
                "required"=> false,
                "default"=> null
            ],
            "qty"=> [
                "description"=> "",
                "name"=> "qty",
                "comment"=> "",
                "type"=> [
                    "proto"=> "varchar",
                    "length"=> "45"
                ],
                "iskey"=> false,
                "required"=> false,
                "default"=> null
            ],
            "unit_price"=> [
                "description"=> "",
                "name"=> "unit_price",
                "comment"=> "",
                "type"=> [
                    "proto"=> "varchar",
                    "length"=> "45"
                ],
                "iskey"=> false,
                "required"=> false,
                "default"=> null
            ],
            "invoices_id"=> [
                "description"=> "",
                "name"=> "invoices_id",
                "comment"=> "",
                "type"=> [
                    "proto"=> "int",
                    "length"=> "10"
                ],
                "iskey"=> false,
                "required"=> true,
                "default"=> null,
                "foreignKey"=> [
                    "table"=> "invoices",
                    "field"=> "id"
                ]
            ],
            "order_item"=> [
                "description"=> "",
                "name"=> "order_item",
                "comment"=> "",
                "type"=> [
                    "proto"=> "int",
                    "length"=> "10"
                ],
                "iskey"=> false,
                "required"=> true,
                "default"=> null,
                "foreignKey"=> [
                    "table"=> "orders_items_subitems",
                    "field"=> "id"
                ]
            ],
            "unit"=> [
                "description"=> "",
                "name"=> "unit",
                "comment"=> "",
                "type"=> [
                    "proto"=> "varchar",
                    "length"=> "20"
                ],
                "iskey"=> false,
                "required"=> true,
                "default"=> null,
                "foreignKey"=> [
                    "table"=> "config_units",
                    "field"=> "unit"
                ]
            ]
        ],
        "name"=> "invoice_items",
        "description"=> "",
        "comment"=> "",
        "type"=> "table",
        "keyFld"=> "id",
        "relations"=> [
            "unit"=> [
                "table"=> "config_units",
                "field"=> "unit",
                "type"=> "outbound",
                "fkfield"=> "unit"
            ],
            "invoices_id"=> [
                "table"=> "invoices",
                "field"=> "id",
                "type"=> "outbound",
                "fkfield"=> "invoices_id"
            ],
            "order_item"=> [
                "table"=> "orders_items_subitems",
                "field"=> "id",
                "type"=> "outbound",
                "fkfield"=> "order_item"
            ]
        ]
    ],
    "invoices"=> [
        "fields"=> [
            "id"=> [
                "description"=> "",
                "name"=> "id",
                "comment"=> "",
                "type"=> [
                    "proto"=> "int",
                    "length"=> "10"
                ],
                "iskey"=> true,
                "required"=> false,
                "default"=> null,
                "referencedBy"=> [
                    [
                        "table"=> "invoice_items",
                        "field"=> "invoices_id"
                    ]
                ]
            ],
            "serie"=> [
                "description"=> "",
                "name"=> "serie",
                "comment"=> "",
                "type"=> [
                    "proto"=> "varchar",
                    "length"=> "45"
                ],
                "iskey"=> false,
                "required"=> false,
                "default"=> null
            ],
            "nr"=> [
                "description"=> "",
                "name"=> "nr",
                "comment"=> "",
                "type"=> [
                    "proto"=> "varchar",
                    "length"=> "45"
                ],
                "iskey"=> false,
                "required"=> false,
                "default"=> null
            ],
            "customer"=> [
                "description"=> "",
                "name"=> "customer",
                "comment"=> "",
                "type"=> [
                    "proto"=> "int",
                    "length"=> "10"
                ],
                "iskey"=> false,
                "required"=> true,
                "default"=> null,
                "foreignKey"=> [
                    "table"=> "partners",
                    "field"=> "id"
                ]
            ],
            "total"=> [
                "description"=> "",
                "name"=> "total",
                "comment"=> "",
                "type"=> [
                    "proto"=> "float"
                ],
                "iskey"=> false,
                "required"=> false,
                "default"=> null
            ],
            "vat"=> [
                "description"=> "",
                "name"=> "vat",
                "comment"=> "",
                "type"=> [
                    "proto"=> "float"
                ],
                "iskey"=> false,
                "required"=> false,
                "default"=> null
            ],
            "date"=> [
                "description"=> "",
                "name"=> "date",
                "comment"=> "",
                "type"=> [
                    "proto"=> "timestamp"
                ],
                "iskey"=> false,
                "required"=> false,
                "default"=> null
            ],
            "orders_id"=> [
                "description"=> "",
                "name"=> "orders_id",
                "comment"=> "",
                "type"=> [
                    "proto"=> "int",
                    "length"=> "10"
                ],
                "iskey"=> false,
                "required"=> true,
                "default"=> null,
                "foreignKey"=> [
                    "table"=> "orders",
                    "field"=> "id"
                ]
            ]
        ],
        "name"=> "invoices",
        "description"=> "",
        "comment"=> "",
        "type"=> "table",
        "keyFld"=> "id",
        "relations"=> [
            "orders_id"=> [
                "table"=> "orders",
                "field"=> "id",
                "type"=> "outbound",
                "fkfield"=> "orders_id"
            ],
            "customer"=> [
                "table"=> "partners",
                "field"=> "id",
                "type"=> "outbound",
                "fkfield"=> "customer"
            ],
            "invoice_items"=> [
                "table"=> "invoice_items",
                "field"=> "invoices_id",
                "type"=> "inbound"
            ]
        ]
    ],
    "orders"=> [
        "fields"=> [
            "id"=> [
                "description"=> "",
                "name"=> "id",
                "comment"=> "",
                "type"=> [
                    "proto"=> "int",
                    "length"=> "10"
                ],
                "iskey"=> true,
                "required"=> false,
                "default"=> null,
                "referencedBy"=> [
                    [
                        "table"=> "invoices",
                        "field"=> "orders_id"
                    ],
                    [
                        "table"=> "orders_items",
                        "field"=> "order_id"
                    ],
                    [
                        "table"=> "receipts",
                        "field"=> "order"
                    ]
                ]
            ],
            "doc_id"=> [
                "description"=> "",
                "name"=> "doc_id",
                "comment"=> "",
                "type"=> [
                    "proto"=> "int",
                    "length"=> "20"
                ],
                "iskey"=> false,
                "required"=> true,
                "default"=> null
            ],
            "partner"=> [
                "description"=> "",
                "name"=> "partner",
                "comment"=> "",
                "type"=> [
                    "proto"=> "int",
                    "length"=> "10"
                ],
                "iskey"=> false,
                "required"=> true,
                "default"=> null,
                "foreignKey"=> [
                    "table"=> "partners",
                    "field"=> "id"
                ]
            ],
            "offer_date"=> [
                "description"=> "",
                "name"=> "offer_date",
                "comment"=> "",
                "type"=> [
                    "proto"=> "timestamp"
                ],
                "iskey"=> false,
                "required"=> false,
                "default"=> null
            ],
            "creation_date"=> [
                "description"=> "",
                "name"=> "creation_date",
                "comment"=> "",
                "type"=> [
                    "proto"=> "timestamp"
                ],
                "iskey"=> false,
                "required"=> false,
                "default"=> "CURRENT_TIMESTAMP"
            ],
            "last_update"=> [
                "description"=> "",
                "name"=> "last_update",
                "comment"=> "",
                "type"=> [
                    "proto"=> "timestamp"
                ],
                "iskey"=> false,
                "required"=> false,
                "default"=> "CURRENT_TIMESTAMP"
            ],
            "estimated_delivery_date"=> [
                "description"=> "",
                "name"=> "estimated_delivery_date",
                "comment"=> "",
                "type"=> [
                    "proto"=> "timestamp"
                ],
                "iskey"=> false,
                "required"=> false,
                "default"=> null
            ],
            "status"=> [
                "description"=> "",
                "name"=> "status",
                "comment"=> "",
                "type"=> [
                    "proto"=> "enum",
                    "vals"=> [
                        "ordered",
                        "processing",
                        "ready",
                        "delivered",
                        "canceled"
                    ]
                ],
                "iskey"=> false,
                "required"=> false,
                "default"=> "ordered"
            ],
            "user_id"=> [
                "description"=> "",
                "name"=> "user_id",
                "comment"=> "",
                "type"=> [
                    "proto"=> "int",
                    "length"=> "10"
                ],
                "iskey"=> false,
                "required"=> true,
                "default"=> null
            ],
            "is_offer"=> [
                "description"=> "",
                "name"=> "is_offer",
                "comment"=> "",
                "type"=> [
                    "proto"=> "tinyint",
                    "length"=> "1"
                ],
                "iskey"=> false,
                "required"=> true,
                "default"=> "0"
            ]
        ],
        "name"=> "orders",
        "description"=> "",
        "comment"=> "",
        "type"=> "table",
        "keyFld"=> "id",
        "relations"=> [
            "partner"=> [
                "table"=> "partners",
                "field"=> "id",
                "type"=> "outbound",
                "fkfield"=> "partner"
            ],
            "invoices"=> [
                "table"=> "invoices",
                "field"=> "orders_id",
                "type"=> "inbound"
            ],
            "orders_items"=> [
                "table"=> "orders_items",
                "field"=> "order_id",
                "type"=> "inbound"
            ],
            "receipts"=> [
                "table"=> "receipts",
                "field"=> "order",
                "type"=> "inbound"
            ]
        ]
    ],
    "orders_items"=> [
        "fields"=> [
            "id"=> [
                "description"=> "",
                "name"=> "id",
                "comment"=> "",
                "type"=> [
                    "proto"=> "int",
                    "length"=> "10"
                ],
                "iskey"=> true,
                "required"=> false,
                "default"=> null,
                "referencedBy"=> [
                    [
                        "table"=> "orders_items_subitems",
                        "field"=> "orders_items_id"
                    ]
                ]
            ],
            "order_id"=> [
                "description"=> "",
                "name"=> "order_id",
                "comment"=> "",
                "type"=> [
                    "proto"=> "int",
                    "length"=> "10"
                ],
                "iskey"=> false,
                "required"=> true,
                "default"=> null,
                "foreignKey"=> [
                    "table"=> "orders",
                    "field"=> "id"
                ]
            ],
            "product_id"=> [
                "description"=> "",
                "name"=> "product_id",
                "comment"=> "",
                "type"=> [
                    "proto"=> "int",
                    "length"=> "10"
                ],
                "iskey"=> false,
                "required"=> true,
                "default"=> null,
                "foreignKey"=> [
                    "table"=> "products",
                    "field"=> "id"
                ]
            ],
            "name"=> [
                "description"=> "",
                "name"=> "name",
                "comment"=> "",
                "type"=> [
                    "proto"=> "varchar",
                    "length"=> "50"
                ],
                "iskey"=> false,
                "required"=> true,
                "default"=> null
            ],
            "qty"=> [
                "description"=> "",
                "name"=> "qty",
                "comment"=> "",
                "type"=> [
                    "proto"=> "float"
                ],
                "iskey"=> false,
                "required"=> true,
                "default"=> null
            ],
            "unit"=> [
                "description"=> "",
                "name"=> "unit",
                "comment"=> "",
                "type"=> [
                    "proto"=> "varchar",
                    "length"=> "20"
                ],
                "iskey"=> false,
                "required"=> true,
                "default"=> null
            ],
            "unit_price"=> [
                "description"=> "",
                "name"=> "unit_price",
                "comment"=> "",
                "type"=> [
                    "proto"=> "float"
                ],
                "iskey"=> false,
                "required"=> true,
                "default"=> null
            ],
            "discount"=> [
                "description"=> "",
                "name"=> "discount",
                "comment"=> "",
                "type"=> [
                    "proto"=> "float"
                ],
                "iskey"=> false,
                "required"=> true,
                "default"=> null
            ],
            "invoiced"=> [
                "description"=> "",
                "name"=> "invoiced",
                "comment"=> "",
                "type"=> [
                    "proto"=> "tinyint",
                    "length"=> "1"
                ],
                "iskey"=> false,
                "required"=> true,
                "default"=> "0"
            ],
            "comments"=> [
                "description"=> "",
                "name"=> "comments",
                "comment"=> "",
                "type"=> [
                    "proto"=> "text"
                ],
                "iskey"=> false,
                "required"=> false,
                "default"=> null
            ]
        ],
        "name"=> "orders_items",
        "description"=> "",
        "comment"=> "",
        "type"=> "table",
        "keyFld"=> "id",
        "relations"=> [
            "order_id"=> [
                "table"=> "orders",
                "field"=> "id",
                "type"=> "outbound",
                "fkfield"=> "order_id"
            ],
            "product_id"=> [
                "table"=> "products",
                "field"=> "id",
                "type"=> "outbound",
                "fkfield"=> "product_id"
            ],
            "orders_items_subitems"=> [
                "table"=> "orders_items_subitems",
                "field"=> "orders_items_id",
                "type"=> "inbound"
            ]
        ]
    ],
    "orders_items_subitems"=> [
        "fields"=> [
            "id"=> [
                "description"=> "",
                "name"=> "id",
                "comment"=> "",
                "type"=> [
                    "proto"=> "int",
                    "length"=> "10"
                ],
                "iskey"=> true,
                "required"=> false,
                "default"=> null,
                "referencedBy"=> [
                    [
                        "table"=> "invoice_items",
                        "field"=> "order_item"
                    ]
                ]
            ],
            "orders_items_id"=> [
                "description"=> "",
                "name"=> "orders_items_id",
                "comment"=> "",
                "type"=> [
                    "proto"=> "int",
                    "length"=> "10"
                ],
                "iskey"=> false,
                "required"=> true,
                "default"=> null,
                "foreignKey"=> [
                    "table"=> "orders_items",
                    "field"=> "id"
                ]
            ],
            "inventory_id"=> [
                "description"=> "",
                "name"=> "inventory_id",
                "comment"=> "",
                "type"=> [
                    "proto"=> "int",
                    "length"=> "10"
                ],
                "iskey"=> false,
                "required"=> true,
                "default"=> null,
                "foreignKey"=> [
                    "table"=> "inventory",
                    "field"=> "id"
                ]
            ],
            "name"=> [
                "description"=> "",
                "name"=> "name",
                "comment"=> "",
                "type"=> [
                    "proto"=> "varchar",
                    "length"=> "50"
                ],
                "iskey"=> false,
                "required"=> false,
                "default"=> null
            ],
            "size_l"=> [
                "description"=> "",
                "name"=> "size_l",
                "comment"=> "",
                "type"=> [
                    "proto"=> "float"
                ],
                "iskey"=> false,
                "required"=> false,
                "default"=> null
            ],
            "size_h"=> [
                "description"=> "",
                "name"=> "size_h",
                "comment"=> "",
                "type"=> [
                    "proto"=> "float"
                ],
                "iskey"=> false,
                "required"=> false,
                "default"=> null
            ],
            "size_w"=> [
                "description"=> "",
                "name"=> "size_w",
                "comment"=> "",
                "type"=> [
                    "proto"=> "float"
                ],
                "iskey"=> false,
                "required"=> false,
                "default"=> null
            ],
            "status"=> [
                "description"=> "",
                "name"=> "status",
                "comment"=> "",
                "type"=> [
                    "proto"=> "enum",
                    "vals"=> [
                        "ordered",
                        "processing",
                        "ready",
                        "delivered"
                    ]
                ],
                "iskey"=> false,
                "required"=> false,
                "default"=> "ordered"
            ],
            "qty"=> [
                "description"=> "",
                "name"=> "qty",
                "comment"=> "",
                "type"=> [
                    "proto"=> "float"
                ],
                "iskey"=> false,
                "required"=> false,
                "default"=> null
            ],
            "unit_price"=> [
                "description"=> "",
                "name"=> "unit_price",
                "comment"=> "",
                "type"=> [
                    "proto"=> "float"
                ],
                "iskey"=> false,
                "required"=> true,
                "default"=> "0"
            ],
            "unit"=> [
                "description"=> "",
                "name"=> "unit",
                "comment"=> "",
                "type"=> [
                    "proto"=> "varchar",
                    "length"=> "20"
                ],
                "iskey"=> false,
                "required"=> false,
                "default"=> null
            ],
            "comment"=> [
                "description"=> "",
                "name"=> "comment",
                "comment"=> "",
                "type"=> [
                    "proto"=> "text"
                ],
                "iskey"=> false,
                "required"=> false,
                "default"=> null
            ]
        ],
        "name"=> "orders_items_subitems",
        "description"=> "",
        "comment"=> "",
        "type"=> "table",
        "keyFld"=> "id",
        "relations"=> [
            "orders_items_id"=> [
                "table"=> "orders_items",
                "field"=> "id",
                "type"=> "outbound",
                "fkfield"=> "orders_items_id"
            ],
            "inventory_id"=> [
                "table"=> "inventory",
                "field"=> "id",
                "type"=> "outbound",
                "fkfield"=> "inventory_id"
            ],
            "invoice_items"=> [
                "table"=> "invoice_items",
                "field"=> "order_item",
                "type"=> "inbound"
            ]
        ]
    ],
    "partners"=> [
        "fields"=> [
            "id"=> [
                "description"=> "",
                "name"=> "id",
                "comment"=> "",
                "type"=> [
                    "proto"=> "int",
                    "length"=> "10"
                ],
                "iskey"=> true,
                "required"=> false,
                "default"=> null,
                "referencedBy"=> [
                    [
                        "table"=> "invoices",
                        "field"=> "customer"
                    ],
                    [
                        "table"=> "orders",
                        "field"=> "partner"
                    ],
                    [
                        "table"=> "receipts",
                        "field"=> "customer"
                    ]
                ]
            ],
            "cod_saga"=> [
                "description"=> "",
                "name"=> "cod_saga",
                "comment"=> "",
                "type"=> [
                    "proto"=> "varchar",
                    "length"=> "255"
                ],
                "iskey"=> false,
                "required"=> false,
                "default"=> null
            ],
            "name"=> [
                "description"=> "",
                "name"=> "name",
                "comment"=> "",
                "type"=> [
                    "proto"=> "varchar",
                    "length"=> "255"
                ],
                "iskey"=> false,
                "required"=> false,
                "default"=> null
            ],
            "cod_fiscal"=> [
                "description"=> "",
                "name"=> "cod_fiscal",
                "comment"=> "",
                "type"=> [
                    "proto"=> "varchar",
                    "length"=> "255"
                ],
                "iskey"=> false,
                "required"=> false,
                "default"=> null
            ],
            "reg_com"=> [
                "description"=> "",
                "name"=> "reg_com",
                "comment"=> "",
                "type"=> [
                    "proto"=> "varchar",
                    "length"=> "255"
                ],
                "iskey"=> false,
                "required"=> false,
                "default"=> null
            ],
            "address"=> [
                "description"=> "",
                "name"=> "address",
                "comment"=> "",
                "type"=> [
                    "proto"=> "varchar",
                    "length"=> "255"
                ],
                "iskey"=> false,
                "required"=> true,
                "default"=> null
            ],
            "city"=> [
                "description"=> "",
                "name"=> "city",
                "comment"=> "",
                "type"=> [
                    "proto"=> "varchar",
                    "length"=> "255"
                ],
                "iskey"=> false,
                "required"=> true,
                "default"=> null
            ],
            "county"=> [
                "description"=> "",
                "name"=> "county",
                "comment"=> "",
                "type"=> [
                    "proto"=> "varchar",
                    "length"=> "255"
                ],
                "iskey"=> false,
                "required"=> true,
                "default"=> null
            ],
            "country"=> [
                "description"=> "",
                "name"=> "country",
                "comment"=> "",
                "type"=> [
                    "proto"=> "varchar",
                    "length"=> "255"
                ],
                "iskey"=> false,
                "required"=> true,
                "default"=> null
            ],
            "analitic"=> [
                "description"=> "",
                "name"=> "analitic",
                "comment"=> "",
                "type"=> [
                    "proto"=> "varchar",
                    "length"=> "255"
                ],
                "iskey"=> false,
                "required"=> false,
                "default"=> null
            ],
            "zs"=> [
                "description"=> "",
                "name"=> "zs",
                "comment"=> "",
                "type"=> [
                    "proto"=> "varchar",
                    "length"=> "255"
                ],
                "iskey"=> false,
                "required"=> false,
                "default"=> null
            ],
            "discount"=> [
                "description"=> "",
                "name"=> "discount",
                "comment"=> "",
                "type"=> [
                    "proto"=> "varchar",
                    "length"=> "255"
                ],
                "iskey"=> false,
                "required"=> false,
                "default"=> null
            ],
            "bank_name"=> [
                "description"=> "",
                "name"=> "bank_name",
                "comment"=> "",
                "type"=> [
                    "proto"=> "varchar",
                    "length"=> "255"
                ],
                "iskey"=> false,
                "required"=> false,
                "default"=> null
            ],
            "bank_account"=> [
                "description"=> "",
                "name"=> "bank_account",
                "comment"=> "",
                "type"=> [
                    "proto"=> "varchar",
                    "length"=> "255"
                ],
                "iskey"=> false,
                "required"=> false,
                "default"=> null
            ],
            "branch"=> [
                "description"=> "",
                "name"=> "branch",
                "comment"=> "",
                "type"=> [
                    "proto"=> "varchar",
                    "length"=> "255"
                ],
                "iskey"=> false,
                "required"=> false,
                "default"=> null
            ],
            "branch_rep"=> [
                "description"=> "",
                "name"=> "branch_rep",
                "comment"=> "",
                "type"=> [
                    "proto"=> "varchar",
                    "length"=> "255"
                ],
                "iskey"=> false,
                "required"=> false,
                "default"=> null
            ],
            "personal_id_serie"=> [
                "description"=> "",
                "name"=> "personal_id_serie",
                "comment"=> "",
                "type"=> [
                    "proto"=> "varchar",
                    "length"=> "255"
                ],
                "iskey"=> false,
                "required"=> false,
                "default"=> null
            ],
            "personal_id_number"=> [
                "description"=> "",
                "name"=> "personal_id_number",
                "comment"=> "",
                "type"=> [
                    "proto"=> "varchar",
                    "length"=> "255"
                ],
                "iskey"=> false,
                "required"=> false,
                "default"=> null
            ],
            "personal_id_issuer"=> [
                "description"=> "",
                "name"=> "personal_id_issuer",
                "comment"=> "",
                "type"=> [
                    "proto"=> "varchar",
                    "length"=> "255"
                ],
                "iskey"=> false,
                "required"=> false,
                "default"=> null
            ],
            "license_plate"=> [
                "description"=> "",
                "name"=> "license_plate",
                "comment"=> "",
                "type"=> [
                    "proto"=> "varchar",
                    "length"=> "255"
                ],
                "iskey"=> false,
                "required"=> false,
                "default"=> null
            ],
            "comments"=> [
                "description"=> "",
                "name"=> "comments",
                "comment"=> "",
                "type"=> [
                    "proto"=> "varchar",
                    "length"=> "255"
                ],
                "iskey"=> false,
                "required"=> false,
                "default"=> null
            ],
            "agent"=> [
                "description"=> "",
                "name"=> "agent",
                "comment"=> "",
                "type"=> [
                    "proto"=> "varchar",
                    "length"=> "255"
                ],
                "iskey"=> false,
                "required"=> false,
                "default"=> null
            ],
            "agent_name"=> [
                "description"=> "",
                "name"=> "agent_name",
                "comment"=> "",
                "type"=> [
                    "proto"=> "varchar",
                    "length"=> "255"
                ],
                "iskey"=> false,
                "required"=> false,
                "default"=> null
            ],
            "group"=> [
                "description"=> "",
                "name"=> "group",
                "comment"=> "",
                "type"=> [
                    "proto"=> "varchar",
                    "length"=> "255"
                ],
                "iskey"=> false,
                "required"=> false,
                "default"=> null
            ],
            "type_tert"=> [
                "description"=> "",
                "name"=> "type_tert",
                "comment"=> "",
                "type"=> [
                    "proto"=> "varchar",
                    "length"=> "255"
                ],
                "iskey"=> false,
                "required"=> false,
                "default"=> null
            ],
            "phone"=> [
                "description"=> "",
                "name"=> "phone",
                "comment"=> "",
                "type"=> [
                    "proto"=> "varchar",
                    "length"=> "255"
                ],
                "iskey"=> false,
                "required"=> false,
                "default"=> null
            ],
            "email"=> [
                "description"=> "",
                "name"=> "email",
                "comment"=> "",
                "type"=> [
                    "proto"=> "varchar",
                    "length"=> "255"
                ],
                "iskey"=> false,
                "required"=> false,
                "default"=> null
            ],
            "is_vat"=> [
                "description"=> "",
                "name"=> "is_vat",
                "comment"=> "",
                "type"=> [
                    "proto"=> "varchar",
                    "length"=> "255"
                ],
                "iskey"=> false,
                "required"=> false,
                "default"=> null
            ],
            "blocked"=> [
                "description"=> "",
                "name"=> "blocked",
                "comment"=> "",
                "type"=> [
                    "proto"=> "varchar",
                    "length"=> "255"
                ],
                "iskey"=> false,
                "required"=> false,
                "default"=> null
            ],
            "date_v_vat"=> [
                "description"=> "",
                "name"=> "date_v_vat",
                "comment"=> "",
                "type"=> [
                    "proto"=> "varchar",
                    "length"=> "255"
                ],
                "iskey"=> false,
                "required"=> false,
                "default"=> null
            ],
            "cb_card"=> [
                "description"=> "",
                "name"=> "cb_card",
                "comment"=> "",
                "type"=> [
                    "proto"=> "varchar",
                    "length"=> "255"
                ],
                "iskey"=> false,
                "required"=> false,
                "default"=> null
            ],
            "date_s_vat"=> [
                "description"=> "",
                "name"=> "date_s_vat",
                "comment"=> "",
                "type"=> [
                    "proto"=> "varchar",
                    "length"=> "255"
                ],
                "iskey"=> false,
                "required"=> false,
                "default"=> null
            ],
            "credit_limit"=> [
                "description"=> "",
                "name"=> "credit_limit",
                "comment"=> "",
                "type"=> [
                    "proto"=> "varchar",
                    "length"=> "255"
                ],
                "iskey"=> false,
                "required"=> false,
                "default"=> null
            ],
            "create_date"=> [
                "description"=> "",
                "name"=> "create_date",
                "comment"=> "",
                "type"=> [
                    "proto"=> "timestamp"
                ],
                "iskey"=> false,
                "required"=> false,
                "default"=> "CURRENT_TIMESTAMP"
            ],
            "is_customer"=> [
                "description"=> "",
                "name"=> "is_customer",
                "comment"=> "",
                "type"=> [
                    "proto"=> "tinyint",
                    "length"=> "1"
                ],
                "iskey"=> false,
                "required"=> false,
                "default"=> "1"
            ],
            "is_supplier"=> [
                "description"=> "",
                "name"=> "is_supplier",
                "comment"=> "",
                "type"=> [
                    "proto"=> "tinyint",
                    "length"=> "1"
                ],
                "iskey"=> false,
                "required"=> true,
                "default"=> "0"
            ]
        ],
        "name"=> "partners",
        "description"=> "",
        "comment"=> "",
        "type"=> "table",
        "keyFld"=> "id",
        "relations"=> [
            "invoices"=> [
                "table"=> "invoices",
                "field"=> "customer",
                "type"=> "inbound"
            ],
            "orders"=> [
                "table"=> "orders",
                "field"=> "partner",
                "type"=> "inbound"
            ],
            "receipts"=> [
                "table"=> "receipts",
                "field"=> "customer",
                "type"=> "inbound"
            ]
        ]
    ],
    "product_items"=> [
        "fields"=> [
            "is_service"=> [
                "description"=> "",
                "name"=> "is_service",
                "comment"=> "",
                "type"=> [
                    "proto"=> "tinyint",
                    "length"=> "1"
                ],
                "iskey"=> false,
                "required"=> false,
                "default"=> "0"
            ],
            "id"=> [
                "description"=> "",
                "name"=> "id",
                "comment"=> "",
                "type"=> [
                    "proto"=> "int",
                    "length"=> "10"
                ],
                "iskey"=> true,
                "required"=> true,
                "default"=> "0"
            ],
            "product_id"=> [
                "description"=> "",
                "name"=> "product_id",
                "comment"=> "",
                "type"=> [
                    "proto"=> "int",
                    "length"=> "10"
                ],
                "iskey"=> false,
                "required"=> true,
                "default"=> null
            ],
            "inventory_id"=> [
                "description"=> "",
                "name"=> "inventory_id",
                "comment"=> "",
                "type"=> [
                    "proto"=> "int",
                    "length"=> "10"
                ],
                "iskey"=> false,
                "required"=> true,
                "default"=> null
            ],
            "qty"=> [
                "description"=> "",
                "name"=> "qty",
                "comment"=> "",
                "type"=> [
                    "proto"=> "float"
                ],
                "iskey"=> false,
                "required"=> false,
                "default"=> null
            ],
            "consumption_factor"=> [
                "description"=> "",
                "name"=> "consumption_factor",
                "comment"=> "",
                "type"=> [
                    "proto"=> "float"
                ],
                "iskey"=> false,
                "required"=> false,
                "default"=> "1"
            ],
            "size_w"=> [
                "description"=> "",
                "name"=> "size_w",
                "comment"=> "",
                "type"=> [
                    "proto"=> "float"
                ],
                "iskey"=> false,
                "required"=> false,
                "default"=> null
            ],
            "size_l"=> [
                "description"=> "",
                "name"=> "size_l",
                "comment"=> "",
                "type"=> [
                    "proto"=> "float"
                ],
                "iskey"=> false,
                "required"=> false,
                "default"=> null
            ],
            "size_h"=> [
                "description"=> "",
                "name"=> "size_h",
                "comment"=> "",
                "type"=> [
                    "proto"=> "float"
                ],
                "iskey"=> false,
                "required"=> false,
                "default"=> null
            ],
            "unit_price"=> [
                "description"=> "",
                "name"=> "unit_price",
                "comment"=> "",
                "type"=> [
                    "proto"=> "int",
                    "length"=> "11"
                ],
                "iskey"=> false,
                "required"=> false,
                "default"=> null
            ],
            "name"=> [
                "description"=> "",
                "name"=> "name",
                "comment"=> "",
                "type"=> [
                    "proto"=> "varchar",
                    "length"=> "45"
                ],
                "iskey"=> false,
                "required"=> false,
                "default"=> null
            ],
            "vendor"=> [
                "description"=> "",
                "name"=> "vendor",
                "comment"=> "",
                "type"=> [
                    "proto"=> "varchar",
                    "length"=> "45"
                ],
                "iskey"=> false,
                "required"=> false,
                "default"=> null
            ],
            "model"=> [
                "description"=> "",
                "name"=> "model",
                "comment"=> "",
                "type"=> [
                    "proto"=> "varchar",
                    "length"=> "45"
                ],
                "iskey"=> false,
                "required"=> false,
                "default"=> null
            ],
            "unit"=> [
                "description"=> "",
                "name"=> "unit",
                "comment"=> "",
                "type"=> [
                    "proto"=> "varchar",
                    "length"=> "20"
                ],
                "iskey"=> false,
                "required"=> false,
                "default"=> null
            ]
        ],
        "relations"=> [],
        "description"=> "",
        "comment"=> "",
        "type"=> "view",
        "keyFld"=> "id"
    ],
    "product_properties"=> [
        "fields"=> [
            "id"=> [
                "description"=> "",
                "name"=> "id",
                "comment"=> "",
                "type"=> [
                    "proto"=> "int",
                    "length"=> "10"
                ],
                "iskey"=> true,
                "required"=> false,
                "default"=> null
            ],
            "name"=> [
                "description"=> "",
                "name"=> "name",
                "comment"=> "",
                "type"=> [
                    "proto"=> "varchar",
                    "length"=> "155"
                ],
                "iskey"=> true,
                "required"=> false,
                "default"=> null
            ],
            "value"=> [
                "description"=> "",
                "name"=> "value",
                "comment"=> "",
                "type"=> [
                    "proto"=> "varchar",
                    "length"=> "155"
                ],
                "iskey"=> false,
                "required"=> false,
                "default"=> null
            ],
            "product_id"=> [
                "description"=> "",
                "name"=> "product_id",
                "comment"=> "",
                "type"=> [
                    "proto"=> "int",
                    "length"=> "10"
                ],
                "iskey"=> false,
                "required"=> true,
                "default"=> null,
                "foreignKey"=> [
                    "table"=> "products",
                    "field"=> "id"
                ]
            ]
        ],
        "name"=> "product_properties",
        "description"=> "",
        "comment"=> "",
        "type"=> "table",
        "keyFld"=> "id",
        "relations"=> [
            "product_id"=> [
                "table"=> "products",
                "field"=> "id",
                "type"=> "outbound",
                "fkfield"=> "product_id"
            ]
        ]
    ],
    "products"=> [
        "fields"=> [
            "id"=> [
                "description"=> "",
                "name"=> "id",
                "comment"=> "",
                "type"=> [
                    "proto"=> "int",
                    "length"=> "10"
                ],
                "iskey"=> true,
                "required"=> false,
                "default"=> null,
                "referencedBy"=> [
                    [
                        "table"=> "orders_items",
                        "field"=> "product_id"
                    ],
                    [
                        "table"=> "product_properties",
                        "field"=> "product_id"
                    ],
                    [
                        "table"=> "products_structure",
                        "field"=> "product_id"
                    ]
                ]
            ],
            "name"=> [
                "description"=> "",
                "name"=> "name",
                "comment"=> "",
                "type"=> [
                    "proto"=> "varchar",
                    "length"=> "45"
                ],
                "iskey"=> false,
                "required"=> false,
                "default"=> null
            ],
            "categories_id"=> [
                "description"=> "",
                "name"=> "categories_id",
                "comment"=> "",
                "type"=> [
                    "proto"=> "int",
                    "length"=> "10"
                ],
                "iskey"=> false,
                "required"=> false,
                "default"=> "1"
            ],
            "active"=> [
                "description"=> "",
                "name"=> "active",
                "comment"=> "",
                "type"=> [
                    "proto"=> "tinyint",
                    "length"=> "1"
                ],
                "iskey"=> false,
                "required"=> false,
                "default"=> "1"
            ],
            "creation_date"=> [
                "description"=> "",
                "name"=> "creation_date",
                "comment"=> "",
                "type"=> [
                    "proto"=> "timestamp"
                ],
                "iskey"=> false,
                "required"=> false,
                "default"=> "CURRENT_TIMESTAMP"
            ],
            "last_update"=> [
                "description"=> "",
                "name"=> "last_update",
                "comment"=> "",
                "type"=> [
                    "proto"=> "timestamp"
                ],
                "iskey"=> false,
                "required"=> false,
                "default"=> "CURRENT_TIMESTAMP"
            ],
            "cont_conta"=> [
                "description"=> "",
                "name"=> "cont_conta",
                "comment"=> "",
                "type"=> [
                    "proto"=> "varchar",
                    "length"=> "10"
                ],
                "iskey"=> false,
                "required"=> false,
                "default"=> null,
                "foreignKey"=> [
                    "table"=> "config_cont_conta",
                    "field"=> "cont"
                ]
            ]
        ],
        "name"=> "products",
        "description"=> "",
        "comment"=> "",
        "type"=> "table",
        "keyFld"=> "id",
        "relations"=> [
            "cont_conta"=> [
                "table"=> "config_cont_conta",
                "field"=> "cont",
                "type"=> "outbound",
                "fkfield"=> "cont_conta"
            ],
            "orders_items"=> [
                "table"=> "orders_items",
                "field"=> "product_id",
                "type"=> "inbound"
            ],
            "product_properties"=> [
                "table"=> "product_properties",
                "field"=> "product_id",
                "type"=> "inbound"
            ],
            "products_structure"=> [
                "table"=> "products_structure",
                "field"=> "product_id",
                "type"=> "inbound"
            ],
            "product_items"=> [
                "table"=> "product_items",
                "field"=> "product_id",
                "type"=> "inbound"
            ]
        ]
    ],
    "products_structure"=> [
        "fields"=> [
            "id"=> [
                "description"=> "",
                "name"=> "id",
                "comment"=> "",
                "type"=> [
                    "proto"=> "int",
                    "length"=> "10"
                ],
                "iskey"=> true,
                "required"=> false,
                "default"=> null
            ],
            "product_id"=> [
                "description"=> "",
                "name"=> "product_id",
                "comment"=> "",
                "type"=> [
                    "proto"=> "int",
                    "length"=> "10"
                ],
                "iskey"=> false,
                "required"=> true,
                "default"=> null,
                "foreignKey"=> [
                    "table"=> "products",
                    "field"=> "id"
                ]
            ],
            "inventory_id"=> [
                "description"=> "",
                "name"=> "inventory_id",
                "comment"=> "",
                "type"=> [
                    "proto"=> "int",
                    "length"=> "10"
                ],
                "iskey"=> false,
                "required"=> true,
                "default"=> null,
                "foreignKey"=> [
                    "table"=> "inventory",
                    "field"=> "id"
                ]
            ],
            "qty"=> [
                "description"=> "",
                "name"=> "qty",
                "comment"=> "",
                "type"=> [
                    "proto"=> "float"
                ],
                "iskey"=> false,
                "required"=> false,
                "default"=> null
            ],
            "consumption_factor"=> [
                "description"=> "",
                "name"=> "consumption_factor",
                "comment"=> "",
                "type"=> [
                    "proto"=> "float"
                ],
                "iskey"=> false,
                "required"=> false,
                "default"=> "1"
            ],
            "size_w"=> [
                "description"=> "",
                "name"=> "size_w",
                "comment"=> "",
                "type"=> [
                    "proto"=> "float"
                ],
                "iskey"=> false,
                "required"=> false,
                "default"=> null
            ],
            "size_l"=> [
                "description"=> "",
                "name"=> "size_l",
                "comment"=> "",
                "type"=> [
                    "proto"=> "float"
                ],
                "iskey"=> false,
                "required"=> false,
                "default"=> null
            ],
            "size_h"=> [
                "description"=> "",
                "name"=> "size_h",
                "comment"=> "",
                "type"=> [
                    "proto"=> "float"
                ],
                "iskey"=> false,
                "required"=> false,
                "default"=> null
            ],
            "creation_date"=> [
                "description"=> "",
                "name"=> "creation_date",
                "comment"=> "",
                "type"=> [
                    "proto"=> "timestamp"
                ],
                "iskey"=> false,
                "required"=> false,
                "default"=> "CURRENT_TIMESTAMP"
            ],
            "last_update"=> [
                "description"=> "",
                "name"=> "last_update",
                "comment"=> "",
                "type"=> [
                    "proto"=> "timestamp"
                ],
                "iskey"=> false,
                "required"=> false,
                "default"=> "CURRENT_TIMESTAMP"
            ],
            "is_service"=> [
                "description"=> "",
                "name"=> "is_service",
                "comment"=> "",
                "type"=> [
                    "proto"=> "tinyint",
                    "length"=> "1"
                ],
                "iskey"=> false,
                "required"=> false,
                "default"=> "0"
            ]
        ],
        "name"=> "products_structure",
        "description"=> "",
        "comment"=> "",
        "type"=> "table",
        "keyFld"=> "id",
        "relations"=> [
            "inventory_id"=> [
                "table"=> "inventory",
                "field"=> "id",
                "type"=> "outbound",
                "fkfield"=> "inventory_id"
            ],
            "product_id"=> [
                "table"=> "products",
                "field"=> "id",
                "type"=> "outbound",
                "fkfield"=> "product_id"
            ]
        ]
    ],
    "receipts"=> [
        "fields"=> [
            "id"=> [
                "description"=> "",
                "name"=> "id",
                "comment"=> "",
                "type"=> [
                    "proto"=> "int",
                    "length"=> "10"
                ],
                "iskey"=> true,
                "required"=> false,
                "default"=> null
            ],
            "type"=> [
                "description"=> "",
                "name"=> "type",
                "comment"=> "",
                "type"=> [
                    "proto"=> "enum",
                    "vals"=> [
                        "cash_receipt",
                        "pos_receipt",
                        "bank"
                    ]
                ],
                "iskey"=> false,
                "required"=> true,
                "default"=> null
            ],
            "doc_id"=> [
                "description"=> "",
                "name"=> "doc_id",
                "comment"=> "",
                "type"=> [
                    "proto"=> "varchar",
                    "length"=> "45"
                ],
                "iskey"=> true,
                "required"=> false,
                "default"=> null
            ],
            "amount"=> [
                "description"=> "",
                "name"=> "amount",
                "comment"=> "",
                "type"=> [
                    "proto"=> "float"
                ],
                "iskey"=> false,
                "required"=> false,
                "default"=> null
            ],
            "order"=> [
                "description"=> "",
                "name"=> "order",
                "comment"=> "",
                "type"=> [
                    "proto"=> "int",
                    "length"=> "10"
                ],
                "iskey"=> false,
                "required"=> false,
                "default"=> null,
                "foreignKey"=> [
                    "table"=> "orders",
                    "field"=> "id"
                ]
            ],
            "customer"=> [
                "description"=> "",
                "name"=> "customer",
                "comment"=> "",
                "type"=> [
                    "proto"=> "int",
                    "length"=> "10"
                ],
                "iskey"=> false,
                "required"=> false,
                "default"=> null,
                "foreignKey"=> [
                    "table"=> "partners",
                    "field"=> "id"
                ]
            ],
            "date"=> [
                "description"=> "",
                "name"=> "date",
                "comment"=> "",
                "type"=> [
                    "proto"=> "timestamp"
                ],
                "iskey"=> false,
                "required"=> false,
                "default"=> "CURRENT_TIMESTAMP"
            ]
        ],
        "name"=> "receipts",
        "description"=> "",
        "comment"=> "",
        "type"=> "table",
        "keyFld"=> "id",
        "relations"=> [
            "customer"=> [
                "table"=> "partners",
                "field"=> "id",
                "type"=> "outbound",
                "fkfield"=> "customer"
            ],
            "order"=> [
                "table"=> "orders",
                "field"=> "id",
                "type"=> "outbound",
                "fkfield"=> "order"
            ]
        ]
    ],
    "settings"=> [
        "fields"=> [
            "id"=> [
                "description"=> "",
                "name"=> "id",
                "comment"=> "",
                "type"=> [
                    "proto"=> "int",
                    "length"=> "10"
                ],
                "iskey"=> true,
                "required"=> false,
                "default"=> null
            ],
            "namespace"=> [
                "description"=> "",
                "name"=> "namespace",
                "comment"=> "",
                "type"=> [
                    "proto"=> "varchar",
                    "length"=> "155"
                ],
                "iskey"=> false,
                "required"=> false,
                "default"=> null
            ],
            "setting"=> [
                "description"=> "",
                "name"=> "setting",
                "comment"=> "",
                "type"=> [
                    "proto"=> "varchar",
                    "length"=> "45"
                ],
                "iskey"=> false,
                "required"=> false,
                "default"=> null
            ],
            "value"=> [
                "description"=> "",
                "name"=> "value",
                "comment"=> "",
                "type"=> [
                    "proto"=> "varchar",
                    "length"=> "255"
                ],
                "iskey"=> false,
                "required"=> false,
                "default"=> null
            ]
        ],
        "name"=> "settings",
        "description"=> "",
        "comment"=> "",
        "type"=> "table",
        "keyFld"=> "id"
    ],
    "users"=> [
        "fields"=> [
            "id"=> [
                "description"=> "",
                "name"=> "id",
                "comment"=> "",
                "type"=> [
                    "proto"=> "int",
                    "length"=> "11"
                ],
                "iskey"=> true,
                "required"=> false,
                "default"=> null
            ],
            "uname"=> [
                "description"=> "",
                "name"=> "uname",
                "comment"=> "",
                "type"=> [
                    "proto"=> "varchar",
                    "length"=> "45"
                ],
                "iskey"=> true,
                "required"=> true,
                "default"=> null
            ],
            "passwordhash"=> [
                "description"=> "",
                "name"=> "passwordhash",
                "comment"=> "",
                "type"=> [
                    "proto"=> "varchar",
                    "length"=> "100"
                ],
                "iskey"=> false,
                "required"=> true,
                "default"=> null
            ],
            "locked"=> [
                "description"=> "",
                "name"=> "locked",
                "comment"=> "",
                "type"=> [
                    "proto"=> "tinyint",
                    "length"=> "1"
                ],
                "iskey"=> false,
                "required"=> false,
                "default"=> "0"
            ],
            "role"=> [
                "description"=> "",
                "name"=> "role",
                "comment"=> "",
                "type"=> [
                    "proto"=> "enum",
                    "vals"=> [
                        "admin",
                        "sales",
                        "prod"
                    ]
                ],
                "iskey"=> false,
                "required"=> false,
                "default"=> "admin"
            ],
            "fname"=> [
                "description"=> "",
                "name"=> "fname",
                "comment"=> "",
                "type"=> [
                    "proto"=> "varchar",
                    "length"=> "20"
                ],
                "iskey"=> false,
                "required"=> true,
                "default"=> null
            ],
            "lname"=> [
                "description"=> "",
                "name"=> "lname",
                "comment"=> "",
                "type"=> [
                    "proto"=> "varchar",
                    "length"=> "20"
                ],
                "iskey"=> false,
                "required"=> true,
                "default"=> null
            ]
        ],
        "name"=> "users",
        "description"=> "",
        "comment"=> "",
        "type"=> "table",
        "keyFld"=> "id"
    ]
];