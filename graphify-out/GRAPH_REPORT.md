# Graph Report - .  (2026-07-11)

## Corpus Check
- Large corpus: 280 files · ~11,783,862 words. Semantic extraction will be expensive (many Claude tokens). Consider running on a subfolder.

## Summary
- 73 nodes · 81 edges · 23 communities
- Extraction: 77% EXTRACTED · 23% INFERRED · 0% AMBIGUOUS · INFERRED: 19 edges (avg confidence: 0.82)
- Token cost: 148,783 input · 0 output

## Community Hubs (Navigation)
- Programs, Blogs & Facilities
- Curriculum & Blog Content
- Admissions & Program Pages
- About, Identity & Values
- Pre-School & Enquiry Flow
- Portals, Testimonials & Edutech

## God Nodes (most connected - your core abstractions)
1. `Home Page (index.html)` - 12 edges
2. `Birla Open Minds International School (BOMIS), Ballari` - 6 edges
3. `Educational Programmes Offering` - 6 edges
4. `Home Page Content (home.md)` - 6 edges
5. `Middle School Program (Classes 6-8)` - 6 edges
6. `About Us Page` - 5 edges
7. `Birla Open Minds International School, Ballari (BOMIS)` - 5 edges
8. `Pre-School Program (Ages 1.5-5)` - 5 edges
9. `Primary School Program (Grades 1-4)` - 5 edges
10. `Middle School Programme (Classes 6-8)` - 4 edges

## Surprising Connections (you probably didn't know these)
- `Home Page (index.html)` --references--> `Blog: CBSE School in Ballari - Smartest Investment`  [INFERRED]
  index.html → blogs/blog1-cbse-school-ballari.html_copy.pdf
- `CBSE Curriculum / Board` --semantically_similar_to--> `Holistic Education`  [INFERRED] [semantically similar]
  blogs/cbse-school-ballari-investment.html → assets/blogs/programs contant/Primary School/details.md
- `Home Page (index.html)` --references--> `Blog: Pre-School Admission in Ballari 2026-27`  [INFERRED]
  index.html → blogs/blog2-preschool-admission-ballari.html_copy.pdf
- `Parent Portal Login Page` --semantically_similar_to--> `Staff Portal Login Page`  [INFERRED] [semantically similar]
  parents-login.html → staff-login.html
- `About Us Page` --references--> `Contact / Admissions Enquiry Page`  [EXTRACTED]
  about.html → contact.html

## Import Cycles
- None detected.

## Hyperedges (group relationships)
- **BOMIS K-12 Programme Offering** — assets_blogs_programs_contant_pre_school_details_program, assets_blogs_programs_contant_primary_school_details_program, assets_blogs_programs_contant_middle_school_details_program, assets_blogs_programs_contant_programs_offering [EXTRACTED 1.00]
- **Blog Journal Collection** — blogs_pre_school_admission_ballari, blogs_smart_classrooms_sports_safety, blogs_cbse_school_ballari_investment, blogs_building_leadership_skills_young_age, blog_page [EXTRACTED 1.00]
- **4 C's Value Framework** — assets_about_us_contant_about_us_four_cs, assets_about_us_contant_about_us_bomis_school, assets_about_us_contant_about_us_trinity_society [EXTRACTED 1.00]
- **BOMIS Educational Program Ladder (Pre-School to Middle School)** — programs_pre_school_program, programs_primary_school_program, programs_middle_school_program, holistic_education [EXTRACTED 1.00]
- **Ballari School Blog Content Cluster** — blog_cbse_school_ballari, blog_preschool_admission_ballari, blog_best_school_ballari_holistic, index_html [INFERRED 0.85]
- **Admissions Conversion Flow** — index_html, enquiry_html, admissions_2026 [INFERRED 0.85]

## Communities (23 total, 0 thin omitted)

### Community 0 - "Programs, Blogs & Facilities"
Cohesion: 0.23
Nodes (12): Blog: Best School in Ballari - Holistic Education, Blog: CBSE School in Ballari - Smartest Investment, CBSE / Board Curriculum, Gallery Page, Holistic Education, Home Page Content (home.md), Middle School Program (Classes 6-8), Primary School Program (Grades 1-4) (+4 more)

### Community 1 - "Curriculum & Blog Content"
Cohesion: 0.24
Nodes (11): Middle School Programme (Classes 6-8), Soul Science, SPEED Specialized Sports Programme, Holistic Education, Primary School Programme (Classes 1-5), Blog Listing Page, Blog: Building Leadership Skills from a Young Age, Blog: Why a CBSE School in Ballari Is the Smartest Investment (+3 more)

### Community 2 - "Admissions & Program Pages"
Cohesion: 0.33
Nodes (10): Admissions 2026-27, Blog: Pre-School Admission in Ballari 2026-27, Admission Enquiry Page, Gee Tee Mascot (Bird), Home Page (index.html), Programs Overview Page, Middle School Programme Page, Pre-School Programme Page (+2 more)

### Community 3 - "About, Identity & Values"
Cohesion: 0.38
Nodes (7): About Us Page, Birla Open Minds International School (BOMIS), Ballari, The 4 C's (Care, Co-operation, Collaboration, Courtesy), Nurturing India's Tomorrow, Birla Institute Teacher Training Programme (ECCEd Diploma), Trinity Society for Community Development, About Us Preview Page

### Community 4 - "Pre-School & Enquiry Flow"
Cohesion: 0.33
Nodes (7): GEE TEE (Pre-School Mascot), Pre-School Programme, Programs Preview Page, Educational Programmes Offering, Blog: Pre-School Admission in Ballari 2026-27, Admissions Enquiry Form (2026), Contact / Admissions Enquiry Page

### Community 5 - "Portals, Testimonials & Edutech"
Cohesion: 0.50
Nodes (5): Birla Edutech, Parent Portal Login Page, Birla Open Minds International School, Ballari (BOMIS), Staff Portal Login Page, Parent Testimonials Page

## Knowledge Gaps
- **10 isolated node(s):** `Birla Institute Teacher Training Programme (ECCEd Diploma)`, `GEE TEE (Pre-School Mascot)`, `SPEED Specialized Sports Programme`, `Smart Classrooms`, `Programs Preview Page` (+5 more)
  These have ≤1 connection - possible missing edges or undocumented components.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `Home Page (index.html)` connect `Admissions & Program Pages` to `Programs, Blogs & Facilities`, `Portals, Testimonials & Edutech`?**
  _High betweenness centrality (0.071) - this node is a cross-community bridge._
- **Why does `Educational Programmes Offering` connect `Pre-School & Enquiry Flow` to `Curriculum & Blog Content`, `About, Identity & Values`?**
  _High betweenness centrality (0.056) - this node is a cross-community bridge._
- **Why does `Birla Open Minds International School (BOMIS), Ballari` connect `About, Identity & Values` to `Pre-School & Enquiry Flow`?**
  _High betweenness centrality (0.039) - this node is a cross-community bridge._
- **Are the 3 inferred relationships involving `Home Page (index.html)` (e.g. with `Home Page Content (home.md)` and `Blog: CBSE School in Ballari - Smartest Investment`) actually correct?**
  _`Home Page (index.html)` has 3 INFERRED edges - model-reasoned connections that need verification._
- **Are the 3 inferred relationships involving `Birla Open Minds International School (BOMIS), Ballari` (e.g. with `About Us Page` and `About Us Preview Page`) actually correct?**
  _`Birla Open Minds International School (BOMIS), Ballari` has 3 INFERRED edges - model-reasoned connections that need verification._
- **Are the 3 inferred relationships involving `Educational Programmes Offering` (e.g. with `Programs Preview Page` and `Birla Open Minds International School (BOMIS), Ballari`) actually correct?**
  _`Educational Programmes Offering` has 3 INFERRED edges - model-reasoned connections that need verification._
- **What connects `Birla Institute Teacher Training Programme (ECCEd Diploma)`, `Nurturing India's Tomorrow`, `GEE TEE (Pre-School Mascot)` to the rest of the system?**
  _11 weakly-connected nodes found - possible documentation gaps or missing edges._