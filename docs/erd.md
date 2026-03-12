# Library App ERD

```mermaid
erDiagram
    USERS ||--o{ LOANS : userID
    BOOKS ||--o{ LOANS : bookID
    USERS ||--o{ PURCHASES : userID
    BOOKS ||--o{ PURCHASES : bookID
    USERS ||--o{ AUDIT_LOGS : user_id

    USERS {
        int userID PK
        varchar_100 userName
        varchar_100 email UK
        varchar_200 paswordHash
        int createdAt
        int updateAt
        enum_user_type userType
    }

    BOOKS {
        int bookID PK
        varchar_100 title
        varchar_100 authorName
        varchar_100 coverUrl
        date firstPublishYear
        int edition
        enum_book_format format
        date createdAt
        date updatedAt
    }

    LOANS {
        int loansID PK
        int userID FK
        int bookID FK
        date dueDate
        enum_loan_status Status
        date createdAt
        date updatedAt
    }

    PURCHASES {
        int puechaseId PK
        int userID FK
        int bookID FK
        decimal_6_2 Price
        date purchaseAt
        date createdAt
    }

    AUDIT_LOGS {
        int id PK
        int user_id FK_nullable
        varchar_20 action
        varchar_50 entity
        int entity_id
        datetime timestamp
    }
```

## Notes

- `USERS.email` has a unique index (`email_unique`).
- SQL exact types:
- `USERS.userName` and `USERS.email` are `varchar(100)`, `USERS.paswordHash` is `varchar(200)`, `USERS.userType` is `set('regular','admin')`.
- `BOOKS.title`, `BOOKS.authorName`, `BOOKS.coverUrl` are `varchar(100)`, `BOOKS.format` is `set('Digital','Physical')`.
- `LOANS.Status` is `set('In progress','Finished')`.
- `PURCHASES.Price` is `decimal(6,2)`.
- `AUDIT_LOGS.action` is `varchar(20)`, `AUDIT_LOGS.entity` is `varchar(50)`, `AUDIT_LOGS.timestamp` is `datetime`.
- `LOANS.userID -> USERS.userID` (`userID_FK_loans`).
- `LOANS.bookID -> BOOKS.bookID` (`bookID_FK_loans`).
- `PURCHASES.userID -> USERS.userID` (`userID_FK`).
- `PURCHASES.bookID -> BOOKS.bookID` (`bookID_FK`).
- `AUDIT_LOGS.user_id -> USERS.userID` (`audit_logs_user_id_fk`, nullable, `ON DELETE SET NULL`).
- Foreign keys use `ON UPDATE RESTRICT`. Most use `ON DELETE RESTRICT`, except `AUDIT_LOGS.user_id` which uses `ON DELETE SET NULL`.
