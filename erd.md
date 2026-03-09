# Library App ERD

```mermaid
erDiagram
    USERS ||--o{ LOANS : userID
    BOOKS ||--o{ LOANS : bookID
    USERS ||--o{ PURCHASES : userID
    BOOKS ||--o{ PURCHASES : bookID

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
```

## Notes

- `USERS.email` has a unique index (`email_unique`).
- SQL exact types:
- `USERS.userName` and `USERS.email` are `varchar(100)`, `USERS.paswordHash` is `varchar(200)`, `USERS.userType` is `set('regular','admin')`.
- `BOOKS.title`, `BOOKS.authorName`, `BOOKS.coverUrl` are `varchar(100)`, `BOOKS.format` is `set('Digital','Physical')`.
- `LOANS.Status` is `set('In progress','Finished')`.
- `PURCHASES.Price` is `decimal(6,2)`.
- `LOANS.userID -> USERS.userID` (`userID_FK_loans`).
- `LOANS.bookID -> BOOKS.bookID` (`bookID_FK_loans`).
- `PURCHASES.userID -> USERS.userID` (`userID_FK`).
- `PURCHASES.bookID -> BOOKS.bookID` (`bookID_FK`).
- All foreign keys are `ON DELETE RESTRICT` and `ON UPDATE RESTRICT`.
