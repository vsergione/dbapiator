<?php
return [
    "assets"=> [
        "fields"=> [
            "aid"=> [
                "description"=> "",
                "name"=> "aid",
                "comment"=> "",
                "type"=> [
                    "proto"=> "varchar",
                    "length"=> "60"
                ],
                "iskey"=> true,
                "required"=> true,
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
                "required"=> true,
                "default"=> null
            ],
            "owner"=> [
                "description"=> "",
                "name"=> "owner",
                "comment"=> "",
                "type"=> [
                    "proto"=> "varchar",
                    "length"=> "50"
                ],
                "iskey"=> false,
                "required"=> true,
                "default"=> null,
                "foreignKey"=> [
                    "table"=> "users",
                    "field"=> "uname"
                ]
            ],
            "sn"=> [
                "description"=> "",
                "name"=> "sn",
                "comment"=> "",
                "type"=> [
                    "proto"=> "varchar",
                    "length"=> "45"
                ],
                "iskey"=> true,
                "required"=> true,
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
                "required"=> true,
                "default"=> null
            ]
        ],
        "name"=> "assets",
        "description"=> "",
        "comment"=> "",
        "type"=> "table",
        "keyFld"=> "aid",
        "relations"=> [
            "owner"=> [
                "table"=> "users",
                "field"=> "uname",
                "type"=> "outbound",
                "fkfield"=> "owner"
            ]
        ]
    ],
    "teams"=> [
        "fields"=> [
            "name"=> [
                "description"=> "",
                "name"=> "name",
                "comment"=> "",
                "type"=> [
                    "proto"=> "varchar",
                    "length"=> "45"
                ],
                "iskey"=> true,
                "required"=> true,
                "default"=> null
            ],
            "teamlead"=> [
                "description"=> "",
                "name"=> "teamlead",
                "comment"=> "",
                "type"=> [
                    "proto"=> "varchar",
                    "length"=> "50"
                ],
                "iskey"=> false,
                "required"=> true,
                "default"=> null,
                "foreignKey"=> [
                    "table"=> "users",
                    "field"=> "uname"
                ]
            ],
            "tid"=> [
                "description"=> "",
                "name"=> "tid",
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
                        "field"=> "team_id"
                    ]
                ]
            ]
        ],
        "name"=> "teams",
        "description"=> "",
        "comment"=> "",
        "type"=> "table",
        "keyFld"=> "tid",
        "relations"=> [
            "teamlead"=> [
                "table"=> "users",
                "field"=> "uname",
                "type"=> "outbound",
                "fkfield"=> "teamlead"
            ],
            "users"=> [
                "table"=> "users",
                "field"=> "team_id",
                "type"=> "inbound"
            ]
        ]
    ],
    "users"=> [
        "fields"=> [
            "bdate"=> [
                "description"=> "",
                "name"=> "bdate",
                "comment"=> "",
                "type"=> [
                    "proto"=> "datetime"
                ],
                "iskey"=> false,
                "required"=> false,
                "default"=> null
            ],
            "cdate"=> [
                "description"=> "",
                "name"=> "cdate",
                "comment"=> "",
                "type"=> [
                    "proto"=> "datetime"
                ],
                "iskey"=> false,
                "required"=> false,
                "default"=> "CURRENT_TIMESTAMP"
            ],
            "fname"=> [
                "description"=> "",
                "name"=> "fname",
                "comment"=> "",
                "type"=> [
                    "proto"=> "varchar",
                    "length"=> "45"
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
                    "length"=> "45"
                ],
                "iskey"=> false,
                "required"=> true,
                "default"=> null
            ],
            "team_id"=> [
                "description"=> "",
                "name"=> "team_id",
                "comment"=> "",
                "type"=> [
                    "proto"=> "int"
                ],
                "iskey"=> false,
                "required"=> true,
                "default"=> null,
                "foreignKey"=> [
                    "table"=> "teams",
                    "field"=> "tid"
                ]
            ],
            "uname"=> [
                "description"=> "",
                "name"=> "uname",
                "comment"=> "",
                "type"=> [
                    "proto"=> "varchar",
                    "length"=> "50"
                ],
                "iskey"=> true,
                "required"=> true,
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
            ]
        ],
        "name"=> "users",
        "description"=> "",
        "comment"=> "",
        "type"=> "table",
        "keyFld"=> "uname",
        "relations"=> [
            "team_id"=> [
                "table"=> "teams",
                "field"=> "tid",
                "type"=> "outbound",
                "fkfield"=> "team_id"
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