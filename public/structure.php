<?php
return [
    "structure"=> [
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
                        "table"=> "orders_items",
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
                    "table"=> "orders_items",
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
                            "field"=> "orders_id"
                        ]
                    ]
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
                    "field"=> "orders_id",
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
                            "table"=> "invoice_items",
                            "field"=> "order_item"
                        ]
                    ]
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
                ],
                "products_structure_id"=> [
                    "description"=> "",
                    "name"=> "products_structure_id",
                    "comment"=> "",
                    "type"=> [
                        "proto"=> "int",
                        "length"=> "10"
                    ],
                    "iskey"=> false,
                    "required"=> true,
                    "default"=> null,
                    "foreignKey"=> [
                        "table"=> "products_structure",
                        "field"=> "id"
                    ]
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
                "discount"=> [
                    "description"=> "",
                    "name"=> "discount",
                    "comment"=> "",
                    "type"=> [
                        "proto"=> "float"
                    ],
                    "iskey"=> false,
                    "required"=> true,
                    "default"=> "0"
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
            "name"=> "orders_items",
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
                "products_structure_id"=> [
                    "table"=> "products_structure",
                    "field"=> "id",
                    "type"=> "outbound",
                    "fkfield"=> "products_structure_id"
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
                        ]
                    ]
                ],
                "name"=> [
                    "description"=> "",
                    "name"=> "name",
                    "comment"=> "",
                    "type"=> [
                        "proto"=> "varchar",
                        "length"=> "155"
                    ],
                    "iskey"=> false,
                    "required"=> true,
                    "default"=> null
                ],
                "cod_fiscal"=> [
                    "description"=> "",
                    "name"=> "cod_fiscal",
                    "comment"=> "",
                    "type"=> [
                        "proto"=> "varchar",
                        "length"=> "20"
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
                        "length"=> "45"
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
                        "length"=> "100"
                    ],
                    "iskey"=> false,
                    "required"=> false,
                    "default"=> null
                ],
                "city"=> [
                    "description"=> "",
                    "name"=> "city",
                    "comment"=> "",
                    "type"=> [
                        "proto"=> "varchar",
                        "length"=> "45"
                    ],
                    "iskey"=> false,
                    "required"=> false,
                    "default"=> null
                ],
                "county"=> [
                    "description"=> "",
                    "name"=> "county",
                    "comment"=> "",
                    "type"=> [
                        "proto"=> "varchar",
                        "length"=> "45"
                    ],
                    "iskey"=> false,
                    "required"=> false,
                    "default"=> null
                ],
                "country"=> [
                    "description"=> "",
                    "name"=> "country",
                    "comment"=> "",
                    "type"=> [
                        "proto"=> "varchar",
                        "length"=> "45"
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
                        "length"=> "45"
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
                        "length"=> "45"
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
                        "length"=> "45"
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
                        "length"=> "100"
                    ],
                    "iskey"=> false,
                    "required"=> false,
                    "default"=> null
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
                    "default"=> "0"
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
                    "required"=> false,
                    "default"=> "0"
                ],
                "discount"=> [
                    "description"=> "",
                    "name"=> "discount",
                    "comment"=> "",
                    "type"=> [
                        "proto"=> "float"
                    ],
                    "iskey"=> false,
                    "required"=> false,
                    "default"=> "0"
                ],
                "blocked"=> [
                    "description"=> "",
                    "name"=> "blocked",
                    "comment"=> "",
                    "type"=> [
                        "proto"=> "tinyint",
                        "length"=> "1"
                    ],
                    "iskey"=> false,
                    "required"=> false,
                    "default"=> "0"
                ],
                "zs"=> [
                    "description"=> "",
                    "name"=> "zs",
                    "comment"=> "",
                    "type"=> [
                        "proto"=> "varchar",
                        "length"=> "50"
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
                        "length"=> "50"
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
                        "length"=> "50"
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
                        "length"=> "10"
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
                        "length"=> "20"
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
                        "length"=> "50"
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
                        "length"=> "20"
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
                        "proto"=> "text"
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
                        "length"=> "50"
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
                        "length"=> "50"
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
                        "length"=> "50"
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
                        "length"=> "50"
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
                        "length"=> "50"
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
                        "proto"=> "tinyint",
                        "length"=> "1"
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
                        "proto"=> "timestamp"
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
                        "proto"=> "timestamp"
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
                        "proto"=> "float"
                    ],
                    "iskey"=> false,
                    "required"=> false,
                    "default"=> "0"
                ],
                "sss"=> [
                    "description"=> "",
                    "name"=> "sss",
                    "comment"=> "",
                    "type"=> [
                        "proto"=> "timestamp"
                    ],
                    "iskey"=> false,
                    "required"=> false,
                    "default"=> null
                ],
                "create_data"=> [
                    "description"=> "",
                    "name"=> "create_data",
                    "comment"=> "",
                    "type"=> [
                        "proto"=> "timestamp"
                    ],
                    "iskey"=> false,
                    "required"=> false,
                    "default"=> "CURRENT_TIMESTAMP"
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
                ]
            ]
        ],
        "payments"=> [
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
                            "receipt",
                            "pos_receipt"
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
                        "proto"=> "varchar",
                        "length"=> "45"
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
                    "default"=> "CURRENT_TIMESTAMP"
                ]
            ],
            "name"=> "payments",
            "description"=> "",
            "comment"=> "",
            "type"=> "table",
            "keyFld"=> "id"
        ],
        "product_items"=> [
            "fields"=> [
                "id"=> [
                    "description"=> "",
                    "name"=> "id",
                    "comment"=> "",
                    "type"=> [
                        "proto"=> "int",
                        "length"=> "10"
                    ],
                    "iskey"=> false,
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
                        "proto"=> "float"
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
            "keyFld"=> null
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
                ]
            ],
            "name"=> "products",
            "description"=> "",
            "comment"=> "",
            "type"=> "table",
            "keyFld"=> "id",
            "relations"=> [
                "product_properties"=> [
                    "table"=> "product_properties",
                    "field"=> "product_id",
                    "type"=> "inbound"
                ],
                "products_structure"=> [
                    "table"=> "products_structure",
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
                    "default"=> null,
                    "referencedBy"=> [
                        [
                            "table"=> "orders_items",
                            "field"=> "products_structure_id"
                        ]
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
                "unit_price"=> [
                    "description"=> "",
                    "name"=> "unit_price",
                    "comment"=> "",
                    "type"=> [
                        "proto"=> "float"
                    ],
                    "iskey"=> false,
                    "required"=> false,
                    "default"=> null
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
                ],
                "orders_items"=> [
                    "table"=> "orders_items",
                    "field"=> "products_structure_id",
                    "type"=> "inbound"
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
    ],
    "permissions"=> [
        "categories"=> [
            "fields"=> [
                "id"=> [
                    "insert"=> false,
                    "update"=> false,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ],
                "name"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ],
                "categories_id"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ]
            ],
            "update"=> true,
            "delete"=> true,
            "insert"=> true,
            "read"=> true,
            "relations"=> [
                "categories_id"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "searchable"=> true
                ],
                "categories"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "searchable"=> true
                ]
            ]
        ],
        "config_invoice_series"=> [
            "fields"=> [
                "id"=> [
                    "insert"=> false,
                    "update"=> false,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ],
                "serie"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ],
                "range_min"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ],
                "range_max"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ],
                "current"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ],
                "active"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ]
            ],
            "update"=> true,
            "delete"=> true,
            "insert"=> true,
            "read"=> true
        ],
        "config_units"=> [
            "fields"=> [
                "unit"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ]
            ],
            "update"=> true,
            "delete"=> true,
            "insert"=> true,
            "read"=> true,
            "relations"=> [
                "inventory"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "searchable"=> true
                ],
                "invoice_items"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "searchable"=> true
                ]
            ]
        ],
        "inventory"=> [
            "fields"=> [
                "id"=> [
                    "insert"=> false,
                    "update"=> false,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ],
                "name"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ],
                "vendor"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ],
                "model"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ],
                "qty"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ],
                "unit"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ],
                "comments"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ]
            ],
            "update"=> true,
            "delete"=> true,
            "insert"=> true,
            "read"=> true,
            "relations"=> [
                "unit"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "searchable"=> true
                ],
                "inventory_logs"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "searchable"=> true
                ],
                "products_structure"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "searchable"=> true
                ]
            ]
        ],
        "inventory_logs"=> [
            "fields"=> [
                "id"=> [
                    "insert"=> false,
                    "update"=> false,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ],
                "dt"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ],
                "inventory_id"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ],
                "type"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ],
                "qty"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ]
            ],
            "update"=> true,
            "delete"=> true,
            "insert"=> true,
            "read"=> true,
            "relations"=> [
                "inventory_id"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "searchable"=> true
                ]
            ]
        ],
        "invoice_items"=> [
            "fields"=> [
                "id"=> [
                    "insert"=> false,
                    "update"=> false,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ],
                "qty"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ],
                "unit_price"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ],
                "invoices_id"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ],
                "order_item"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ],
                "unit"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ]
            ],
            "update"=> true,
            "delete"=> true,
            "insert"=> true,
            "read"=> true,
            "relations"=> [
                "unit"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "searchable"=> true
                ],
                "invoices_id"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "searchable"=> true
                ],
                "order_item"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "searchable"=> true
                ]
            ]
        ],
        "invoices"=> [
            "fields"=> [
                "id"=> [
                    "insert"=> false,
                    "update"=> false,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ],
                "serie"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ],
                "nr"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ],
                "customer"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ],
                "total"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ],
                "vat"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ],
                "date"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ],
                "orders_id"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ]
            ],
            "update"=> true,
            "delete"=> true,
            "insert"=> true,
            "read"=> true,
            "relations"=> [
                "orders_id"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "searchable"=> true
                ],
                "customer"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "searchable"=> true
                ],
                "invoice_items"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "searchable"=> true
                ]
            ]
        ],
        "orders"=> [
            "fields"=> [
                "id"=> [
                    "insert"=> false,
                    "update"=> false,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ],
                "partner"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ],
                "date"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ],
                "status"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ]
            ],
            "update"=> true,
            "delete"=> true,
            "insert"=> true,
            "read"=> true,
            "relations"=> [
                "partner"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "searchable"=> true
                ],
                "invoices"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "searchable"=> true
                ],
                "orders_items"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "searchable"=> true
                ]
            ]
        ],
        "orders_items"=> [
            "fields"=> [
                "id"=> [
                    "insert"=> false,
                    "update"=> false,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ],
                "orders_id"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ],
                "products_structure_id"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ],
                "size_l"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ],
                "size_h"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ],
                "size_w"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ],
                "status"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ],
                "qty"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ],
                "unit_price"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ],
                "discount"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ],
                "last_update"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ],
                "comment"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ]
            ],
            "update"=> true,
            "delete"=> true,
            "insert"=> true,
            "read"=> true,
            "relations"=> [
                "orders_id"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "searchable"=> true
                ],
                "products_structure_id"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "searchable"=> true
                ],
                "invoice_items"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "searchable"=> true
                ]
            ]
        ],
        "partners"=> [
            "fields"=> [
                "id"=> [
                    "insert"=> false,
                    "update"=> false,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ],
                "name"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ],
                "cod_fiscal"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ],
                "reg_com"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ],
                "address"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ],
                "city"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ],
                "county"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ],
                "country"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ],
                "bank_name"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ],
                "bank_account"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ],
                "phone"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ],
                "email"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ],
                "is_customer"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ],
                "is_supplier"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ],
                "discount"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ],
                "blocked"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ],
                "zs"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ],
                "branch"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ],
                "branch_rep"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ],
                "personal_id_serie"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ],
                "personal_id_number"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ],
                "personal_id_issuer"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ],
                "license_plate"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ],
                "comments"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ],
                "agent"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ],
                "agent_name"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ],
                "group"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ],
                "type_tert"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ],
                "cb_card"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ],
                "is_vat"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ],
                "date_v_vat"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ],
                "date_s_vat"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ],
                "credit_limit"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ],
                "sss"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ],
                "create_data"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ]
            ],
            "update"=> true,
            "delete"=> true,
            "insert"=> true,
            "read"=> true,
            "relations"=> [
                "invoices"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "searchable"=> true
                ],
                "orders"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "searchable"=> true
                ]
            ]
        ],
        "payments"=> [
            "fields"=> [
                "id"=> [
                    "insert"=> false,
                    "update"=> false,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ],
                "type"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ],
                "doc_id"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ],
                "amount"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ],
                "order"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ],
                "customer"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ],
                "date"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ]
            ],
            "update"=> true,
            "delete"=> true,
            "insert"=> true,
            "read"=> true
        ],
        "product_items"=> [
            "fields"=> [
                "id"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ],
                "product_id"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ],
                "inventory_id"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ],
                "qty"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ],
                "consumption_factor"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ],
                "size_w"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ],
                "size_l"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ],
                "size_h"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ],
                "unit_price"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ],
                "name"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ],
                "vendor"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ],
                "model"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ],
                "unit"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ]
            ],
            "relations"=> [],
            "update"=> false,
            "delete"=> false,
            "insert"=> true,
            "read"=> true
        ],
        "product_properties"=> [
            "fields"=> [
                "id"=> [
                    "insert"=> false,
                    "update"=> false,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ],
                "name"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ],
                "value"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ],
                "product_id"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ]
            ],
            "update"=> true,
            "delete"=> true,
            "insert"=> true,
            "read"=> true,
            "relations"=> [
                "product_id"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "searchable"=> true
                ]
            ]
        ],
        "products"=> [
            "fields"=> [
                "id"=> [
                    "insert"=> false,
                    "update"=> false,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ],
                "name"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ],
                "categories_id"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ],
                "active"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ]
            ],
            "update"=> true,
            "delete"=> true,
            "insert"=> true,
            "read"=> true,
            "relations"=> [
                "product_properties"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "searchable"=> true
                ],
                "products_structure"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "searchable"=> true
                ]
            ]
        ],
        "products_structure"=> [
            "fields"=> [
                "id"=> [
                    "insert"=> false,
                    "update"=> false,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ],
                "product_id"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ],
                "inventory_id"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ],
                "qty"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ],
                "consumption_factor"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ],
                "size_w"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ],
                "size_l"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ],
                "size_h"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ],
                "unit_price"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ]
            ],
            "update"=> true,
            "delete"=> true,
            "insert"=> true,
            "read"=> true,
            "relations"=> [
                "inventory_id"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "searchable"=> true
                ],
                "product_id"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "searchable"=> true
                ],
                "orders_items"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "searchable"=> true
                ]
            ]
        ],
        "settings"=> [
            "fields"=> [
                "id"=> [
                    "insert"=> false,
                    "update"=> false,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ],
                "namespace"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ],
                "setting"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ],
                "value"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ]
            ],
            "update"=> true,
            "delete"=> true,
            "insert"=> true,
            "read"=> true
        ],
        "users"=> [
            "fields"=> [
                "id"=> [
                    "insert"=> false,
                    "update"=> false,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ],
                "uname"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ],
                "passwordhash"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ],
                "locked"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ],
                "role"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ],
                "fname"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ],
                "lname"=> [
                    "insert"=> true,
                    "update"=> true,
                    "select"=> true,
                    "sortable"=> true,
                    "searchable"=> true
                ]
            ],
            "update"=> true,
            "delete"=> true,
            "insert"=> true,
            "read"=> true
        ]
    ]
];