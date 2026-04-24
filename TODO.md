# Category & City Pages Implementation Plan

## Status: In Progress ✅

### 1. [✅ COMPLETED] Create category.php
   - Dedicated SEO-optimized category page
   - Hero with category image/description/posting count
   - Filtered postings via Posting::getAll($category)
   - Dynamic meta/JSON-LD
   - Breadcrumb: Home > Category Name
   - Reuse search form (pre-select category)

### 2. [✅ COMPLETED] Create city.php
   - Similar to category.php but for cities
   - No city table → dynamic city page using Posting::getAll(null, null, null, $city)
   - Hero/header with city name + state detection
   - SEO: "{City} Listings/Services - {APP_NAME}"

### 3. [PENDING] Update .htaccess
   ```
   RewriteRule ^category/([^/]+)/?$ category.php?category=$1 [L,QSA]
   RewriteRule ^city/([^/]+)/?$ city.php?city=$1 [L,QSA]
   ```

### 4. [✅ COMPLETED] Update index.php category links
   - `search.php?category=` → `category/{name}`

### 5. [PENDING] Update search.php links
   - Add category/city pretty URLs in filters

### 6. [✅ COMPLETED] Update index_x.php (if used)

### 7. [PENDING] Test & Verify
   - /category/Electronics → category page loads
   - /city/Mumbai → city page loads  
   - SEO meta/tags correct
   - Responsive design
   - Posting counts accurate

### 8. [COMPLETED] ✅ Create this TODO.md
