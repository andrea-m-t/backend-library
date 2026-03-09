# Library App ERD

```mermaid
erDiagram
    USERS ||--o{ LOANS : userID
    BOOKS ||--o{ LOANS : bookID
    USERS ||--o{ PURCHASES : userID
    BOOKS ||--o{ PURCHASES : bookID

    USERS {
        int userID PK
        varchar(100) userName
        varchar(100) email UK
        varchar(200) paswordHash
        int createdAt
        int updateAt
        set('regular','admin') userType
    }

    BOOKS {
        int bookID PK
        varchar(100) title
        varchar(100) authorName
        varchar(100) coverUrl
        date firstPublishYear
        int edition
        set('Digital','Physical') format
        date createdAt
        date updatedAt
    }

    LOANS {
        int loansID PK
        int userID FK
        int bookID FK
        date dueDate
        set('In progress','Finished') Status
        date createdAt
        date updatedAt
    }

    PURCHASES {
        int puechaseId PK
        int userID FK
        int bookID FK
        decimal(6,2) Price
        date purchaseAt
        date createdAt
    }
```

## Notes

- `USERS.email` has a unique index (`email_unique`).
- `LOANS.userID -> USERS.userID` (`userID_FK_loans`).
- `LOANS.bookID -> BOOKS.bookID` (`bookID_FK_loans`).
- `PURCHASES.userID -> USERS.userID` (`userID_FK`).
- `PURCHASES.bookID -> BOOKS.bookID` (`bookID_FK`).
- All foreign keys are `ON DELETE RESTRICT` and `ON UPDATE RESTRICT`.
