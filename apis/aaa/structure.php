<?php
return [
    "asset_type"=> [
        "fields"=> [
            "type"=> [
                "description"=> "",
                "name"=> "type",
                "comment"=> "",
                "type"=> [
                    "proto"=> "varchar",
                    "length"=> "25"
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
    "assets"=> [
        "fields"=> [
            "id"=> [
                "description"=> "",
                "name"=> "id",
                "comment"=> "",
                "type"=> [
                    "proto"=> "int"
                ],
                "iskey"=> true,
                "required"=> false,
                "default"=> null
            ],
            "make"=> [
                "description"=> "",
                "name"=> "make",
                "comment"=> "",
                "type"=> [
                    "proto"=> "text"
                ],
                "iskey"=> false,
                "required"=> true,
                "default"=> null
            ],
            "model"=> [
                "description"=> "",
                "name"=> "model",
                "comment"=> "",
                "type"=> [
                    "proto"=> "text"
                ],
                "iskey"=> false,
                "required"=> true,
                "default"=> null
            ],
            "owner"=> [
                "description"=> "",
                "name"=> "owner",
                "comment"=> "",
                "type"=> [
                    "proto"=> "int"
                ],
                "iskey"=> false,
                "required"=> false,
                "default"=> null,
                "foreignKey"=> [
                    "table"=> "users",
                    "field"=> "id"
                ]
            ],
            "sn"=> [
                "description"=> "",
                "name"=> "sn",
                "comment"=> "",
                "type"=> [
                    "proto"=> "text"
                ],
                "iskey"=> false,
                "required"=> true,
                "default"=> null
            ],
            "type"=> [
                "description"=> "",
                "name"=> "type",
                "comment"=> "",
                "type"=> [
                    "proto"=> "varchar",
                    "length"=> "25"
                ],
                "iskey"=> false,
                "required"=> false,
                "default"=> null
            ]
        ],
        "name"=> "assets",
        "description"=> "",
        "comment"=> "",
        "type"=> "table",
        "keyFld"=> "id",
        "relations"=> [
            "owner"=> [
                "table"=> "users",
                "field"=> "id",
                "type"=> "outbound", 1:1, 1:n, n:1, n:m
                "fkfield"=> "owner"
            ]
        ]
    ],
    "team_count"=> [
        "fields"=> [
            "cnt"=> [
                "description"=> "",
                "name"=> "cnt",
                "comment"=> "",
                "type"=> [
                    "proto"=> "bigint"
                ],
                "iskey"=> false,
                "required"=> true,
                "default"=> "0"
            ],
            "team"=> [
                "description"=> "",
                "name"=> "team",
                "comment"=> "",
                "type"=> [
                    "proto"=> "int"
                ],
                "iskey"=> true,
                "required"=> false,
                "default"=> null
            ]
        ],
        "relations"=> [],
        "description"=> "",
        "comment"=> "",
        "type"=> "view",
        "keyFld"=> "team"
    ],
    "teams"=> [
        "fields"=> [
            "comments"=> [
                "description"=> "",
                "name"=> "comments",
                "comment"=> "",
                "type"=> [
                    "proto"=> "text"
                ],
                "iskey"=> false,
                "required"=> true,
                "default"=> null
            ],
            "id"=> [
                "description"=> "",
                "name"=> "id",
                "comment"=> "",
                "type"=> [
                    "proto"=> "int"
                ],
                "iskey"=> true,
                "required"=> false,
                "default"=> null,
                "referencedBy"=> [
                    [
                        "table"=> "users",
                        "field"=> "team"
                    ]
                ]
            ],
            "name"=> [
                "description"=> "",
                "name"=> "name",
                "comment"=> "",
                "type"=> [
                    "proto"=> "varchar",
                    "length"=> "25"
                ],
                "iskey"=> false,
                "required"=> true,
                "default"=> null
            ],
            "teamlead"=> [
                "description"=> "",
                "name"=> "teamlead",
                "comment"=> "",
                "type"=> [
                    "proto"=> "int"
                ],
                "iskey"=> false,
                "required"=> false,
                "default"=> null,
                "foreignKey"=> [
                    "table"=> "users",
                    "field"=> "id"
                ]
            ]
        ],
        "name"=> "teams",
        "description"=> "",
        "comment"=> "",
        "type"=> "table",
        "keyFld"=> "id",
        "relations"=> [
            "teamlead"=> [
                "table"=> "users",
                "field"=> "id",
                "type"=> "outbound",
                "fkfield"=> "teamlead"
            ],
            "users"=> [
                "table"=> "users",
                "field"=> "team",
                "type"=> "inbound"
            ],
            "team_count"=> [
                "table"=> "team_count",
                "field"=> "team",
                "type"=> "inbound"
            ]
        ]
    ],
    "users"=> [
        "fields"=> [
            "fname"=> [
                "description"=> "",
                "name"=> "fname",
                "comment"=> "",
                "type"=> [
                    "proto"=> "varchar",
                    "length"=> "25"
                ],
                "iskey"=> false,
                "required"=> true,
                "default"=> null
            ],
            "id"=> [
                "description"=> "",
                "name"=> "id",
                "comment"=> "",
                "type"=> [
                    "proto"=> "int"
                ],
                "iskey"=> true,
                "required"=> false,
                "default"=> null,
                "referencedBy"=> [
                    [
                        "table"=> "assets",
                        "field"=> "owner"
                    ],
                    [
                        "table"=> "teams",
                        "field"=> "teamlead"
                    ]
                ]
            ],
            "lname"=> [
                "description"=> "",
                "name"=> "lname",
                "comment"=> "",
                "type"=> [
                    "proto"=> "varchar",
                    "length"=> "25"
                ],
                "iskey"=> false,
                "required"=> true,
                "default"=> null
            ],
            "team"=> [
                "description"=> "",
                "name"=> "team",
                "comment"=> "",
                "type"=> [
                    "proto"=> "int"
                ],
                "iskey"=> false,
                "required"=> false,
                "default"=> null,
                "foreignKey"=> [
                    "table"=> "teams",
                    "field"=> "id"
                ]
            ]
        ],
        "name"=> "users",
        "description"=> "",
        "comment"=> "",
        "type"=> "table",
        "keyFld"=> "id",
        "relations"=> [
            "team"=> [
                "table"=> "teams",
                "field"=> "id",
                "type"=> "outbound",
                "fkfield"=> "team"
            ],
            "assets"=> [
                "table"=> "assets",
                "field"=> "owner",
                "type"=> "inbound"
            ],
            "teams"=> [
                "table"=> "teams",
                "field"=> "teamlead",
                "type"=> "inbound"
            ]
        ]
    ]
];
