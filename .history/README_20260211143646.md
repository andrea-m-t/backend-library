# Library App

## Overview
Library App is a Single Page Application (SPA) built with React + Vite for a startup pitch challenge.  
The app allows users to:
- Browse a public-domain catalog from OpenLibrary
- Log in with mock authentication
- Borrow books
- Buy ebooks
- Manage borrowed and purchased lists

The project includes routing, global state management with React Context, CRUD operations, API consumption with Axios, responsive UI, and error handling with fallback states and toasts.

## Instructions
### Requirements
- Node.js 18+ (recommended)
- npm

### Setup
1. Install dependencies:
```bash
npm install
```
2. Create environment file:
```bash
cp .env.example .env
```
3. Run development server:
```bash
npm run dev
```
4. Open:
`http://localhost:5173`

### Build for production
```bash
npm run build
```

### Preview production build
```bash
npm run preview
```

## Logic Flow (Technical)
1. App bootstraps in `src/main.jsx` with `BrowserRouter` and `AppStateProvider`.
2. Global state (`src/context/AppStateContext.js`) initializes from `localStorage`:
   - `isLoggedIn`
   - `currentUser`
   - `borrowedBooks`
   - `purchasedBooks`
   - `catalogEbooks`
3. Routes:
   - `/` Home
   - `/catalog` Catalog
   - `/login` Account/Login
   - `/aboutUs` About Us
4. Data fetching:
   - `src/App.js` and `src/catalog.js` call OpenLibrary via Axios using `VITE_` env variables.
5. CRUD behavior:
   - Create: borrow/buy items
   - Read: render borrowed/purchased/catalog lists
   - Update: renew due date (`+7 days`)
   - Delete: return/remove items
6. Error handling:
   - Loading states and fallback UI for failed API calls
   - Global toasts from context for errors and user actions

## Logic Flow (Non Technical)
1. User enters the app and sees featured books.
2. User explores the catalog and opens a book detail.
3. If not logged in, user is prompted to log in.
4. Once logged in, user can:
   - Add books to borrowed
   - Buy ebooks
   - Renew borrowed due dates
   - Return/remove books
5. The app remembers data between sessions using browser storage.

## Authors
- Diego - Product Owner & Developer
- Andrea - Scrum Master & Developer
