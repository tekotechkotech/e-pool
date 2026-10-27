# Graph Report - c:/FAIZ/dev/baznas/e-disday  (2026-08-05)

## Corpus Check
- Large corpus: 121 files À ~526,551 words. Semantic extraction will be expensive (many Claude tokens). Consider running on a subfolder.

## Summary
- 767 nodes · 995 edges · 109 communities (88 shown, 21 thin omitted)
- Extraction: 96% EXTRACTED · 4% INFERRED · 0% AMBIGUOUS · INFERRED: 41 edges (avg confidence: 0.79)
- Token cost: 0 input · 0 output

## Community Hubs (Navigation)
- Public Vendor
- App Models
- Composer
- Public Vendor
- Addpysumberdana
- App Models
- Composer Scripts
- Concurrently
- App Models
- App Models
- App Models
- App Exports
- App Models
- Batalimport
- App Models
- Database Seeders
- Public Vendor
- Clearpersonselection
- Closeprogrammodal
- App Models
- Public Vendor
- App Http
- App Models
- Cariperson
- Public Vendor
- App Providers
- Closedanamodal
- App Exports
- Public Vendor
- Delete P
- Delete Person
- Public Vendor
- Public Vendor
- Public Vendor
- Closeimportmodal
- Database Factories
- Public Vendor
- App Imports
- Deletelampiran Lamp
- Illuminate Foundation
- Opendetailmodal Upz
- Public Vendor
- Public Vendor
- Public Vendor
- Detail
- Public Images
- Readme Agentic
- Resources Views
- App Http
- Public Vendor
- Public Vendor
- Public Vendor
- Public Vendor
- Public Vendor
- Public Vendor
- Readme Eloquent
- Readme Event
-  Yarnrc
- Public Robots

## God Nodes (most connected - your core abstractions)
1. `PengajuanBantuan` - 24 edges
2. `Lembaga` - 15 edges
3. `Person` - 15 edges
4. `User` - 13 edges
5. `Wilayah` - 11 edges
6. `PengajuanLpjBa` - 10 edges
7. `PengajuanBantuanImportTemplate` - 9 edges
8. `Mustahik` - 9 edges
9. `PengajuanMustahik` - 9 edges
10. `Pengurus` - 9 edges

## Surprising Connections (you probably didn't know these)
- `Asnaf` --inherits--> `Illuminate\Database\Eloquent\Model`  [EXTRACTED]
  app/Models/Asnaf.php →   _Bridges community 27 → community 10_
- `Dana` --inherits--> `Illuminate\Database\Eloquent\Model`  [EXTRACTED]
  app/Models/Dana.php →   _Bridges community 22 → community 10_
- `DanaTransaksi` --inherits--> `Illuminate\Database\Eloquent\Model`  [EXTRACTED]
  app/Models/DanaTransaksi.php →   _Bridges community 1 → community 10_
- `Lembaga` --mixes_in--> `Illuminate\Database\Eloquent\Factories\HasFactory`  [EXTRACTED]
  app/Models/Lembaga.php →   _Bridges community 9 → community 12_
- `Lembaga` --inherits--> `Illuminate\Database\Eloquent\Model`  [EXTRACTED]
  app/Models/Lembaga.php →   _Bridges community 9 → community 10_

## Import Cycles
- None detected.

## Hyperedges (group relationships)
- **Laravel Core Framework Architecture Features** — readme_eloquent_orm, readme_schema_migrations, readme_queue_job_processing, readme_event_broadcasting [INFERRED 0.85]
- **BAZNAS Visual Identity** — public_images_logo_baznas_logo, public_images_logo_baznas_garuda_pancasila, public_images_logo_baznas_organization_branding [INFERRED 0.85]

## Communities (109 total, 21 thin omitted)

### Community 0 - "Public Vendor"
Cohesion: 0.04
Nodes (3): TODO: test for directories (should not be allowed), TODO: if we can, throw error to indicate the items cannot by dropped, TODO: see if we can optimize this by measuring root rect

### Community 1 - "App Models"
Cohesion: 0.06
Nodes (11): DanaTransaksi, Muzakki, PengajuanLpjBa, PengajuanMustahik, PengajuanPenyaluran, PengajuanPersetujuan, PengajuanSurvey, Pengurus (+3 more)

### Community 2 - "Composer"
Cohesion: 0.04
Nodes (44): pestphp/pest-plugin, php-http/discovery, autoload, autoload-dev, psr-4, psr-4, config, allow-plugins (+36 more)

### Community 4 - "Addpysumberdana"
Cohesion: 0.06
Nodes (31): addPySumberDana, addReferensiDana, ajukanPengajuan, closeAjukanModal, closeConfirmModal, closeMustahikModal, confirmAjukan, deleteLampiranSurvey({{ $lamp->id }}) (+23 more)

### Community 5 - "App Models"
Cohesion: 0.09
Nodes (5): PengajuanBantuan, PengajuanDisposisi, PengajuanService, Illuminate\Database\Eloquent\Relations\HasOne, Illuminate\Database\Eloquent\Relations\MorphOne

### Community 6 - "Composer Scripts"
Cohesion: 0.08
Nodes (26): scripts, dev, post-autoload-dump, post-create-project-cmd, post-root-package-install, post-update-cmd, pre-package-uninstall, setup (+18 more)

### Community 7 - "Concurrently"
Cohesion: 0.09
Nodes (22): concurrently, laravel-vite-plugin, devDependencies, concurrently, laravel-vite-plugin, prettier, prettier-plugin-blade, tailwindcss (+14 more)

### Community 8 - "App Models"
Cohesion: 0.14
Nodes (4): PengurusJabatan, Person, LembagaSeeder, UserPengurusSeeder

### Community 9 - "App Models"
Cohesion: 0.12
Nodes (3): Lembaga, Mustahik, Illuminate\Database\Eloquent\Relations\BelongsToMany

### Community 10 - "App Models"
Cohesion: 0.19
Nodes (4): Lampiran, MustahikSeeder, PengajuanBantuanSeeder, Illuminate\Database\Eloquent\Model

### Community 11 - "App Exports"
Cohesion: 0.20
Nodes (8): MustahikTemplateExport, PengajuanBantuanImportTemplate, Maatwebsite\Excel\Concerns\ShouldAutoSize, Maatwebsite\Excel\Concerns\WithEvents, Maatwebsite\Excel\Concerns\WithHeadings, Maatwebsite\Excel\Concerns\WithStyles, Maatwebsite\Excel\Concerns\WithTitle, PhpOffice\PhpSpreadsheet\Worksheet\Worksheet

### Community 12 - "App Models"
Cohesion: 0.16
Nodes (5): PengajuanSurveyDetail, User, Illuminate\Database\Eloquent\Factories\HasFactory, Illuminate\Foundation\Auth\User, Illuminate\Notifications\Notifiable

### Community 13 - "Batalimport"
Cohesion: 0.13
Nodes (14): batalImport, closePersonModal, downloadTemplate(, openLampiranModal({{ $index }}, , openPersonModal({{$index}}), removeLampiran({{ $index }}, , removeRow({{ $index }}), closeLampiranModal (+6 more)

### Community 14 - "App Models"
Cohesion: 0.18
Nodes (3): Program, Subprogram, ProgramSubprogramSeeder

### Community 15 - "Database Seeders"
Cohesion: 0.21
Nodes (5): DatabaseSeeder, PengurusJabatanSeeder, UserSeeder, WilayahSeeder, Illuminate\Database\Seeder

### Community 16 - "Public Vendor"
Cohesion: 0.18
Nodes (13): _coalesceClassElements(), _coalesceGetterSetter(), create(), _createElementDescriptor(), _decorate(), find(), _getDecoratorsApi(), _hasDecorators() (+5 more)

### Community 17 - "Clearpersonselection"
Cohesion: 0.17
Nodes (8): clearPersonSelection, delete({{ $mustahik->id }}), detail({{ $mustahik->id }}), edit({{ $mustahik->id }}), components.import-mustahik-modal, openImportModal, selectPerson({{ $person->id }}, , toggleNewPerson

### Community 18 - "Closeprogrammodal"
Cohesion: 0.17
Nodes (5): confirmDeleteSubprogram({{ $sub->id }}), editSubprogram({{ $sub->id }}), moveDownAsnaf({{ $asnaf->id }}), moveUpAsnaf({{ $asnaf->id }}), selectProgram({{ $program->id }})

### Community 20 - "Public Vendor"
Cohesion: 0.22
Nodes (11): _arrayWithHoles(), _arrayWithoutHoles(), _iterableToArray(), _iterableToArrayLimit(), _iterableToArrayLimitLoose(), _nonIterableRest(), _nonIterableSpread(), _slicedToArray() (+3 more)

### Community 21 - "App Http"
Cohesion: 0.27
Nodes (4): RoleMiddleware, Closure, Illuminate\Http\Request, Symfony\Component\HttpFoundation\Response

### Community 22 - "App Models"
Cohesion: 0.29
Nodes (3): Dana, DanaSeeder, Illuminate\Database\Eloquent\Relations\HasMany

### Community 23 - "Cariperson"
Cohesion: 0.20
Nodes (9): cariPerson, cariUpz, {{ $jenis_permohonan == , kembali, removeTempLampiran({{ $index }}), closeLampiranModal, openLampiranModal, saveTempLampiran (+1 more)

### Community 24 - "Public Vendor"
Cohesion: 0.24
Nodes (10): _coalesceClassElements(), _coalesceGetterSetter(), _createElementDescriptor(), _decorate(), _getDecoratorsApi(), _hasDecorators(), _isDataDescriptor(), _optionalCallableProperty() (+2 more)

### Community 25 - "App Providers"
Cohesion: 0.28
Nodes (3): AppServiceProvider, VoltServiceProvider, Illuminate\Support\ServiceProvider

### Community 28 - "Public Vendor"
Cohesion: 0.25
Nodes (8): _defineProperty(), _get(), _getPrototypeOf(), _objectSpread(), _objectSpread2(), ownKeys(), set(), _superPropBase()

### Community 29 - "Delete P"
Cohesion: 0.29
Nodes (3): delete({{ $p->id }}), edit({{ $p->id }}), $set(

### Community 30 - "Delete Person"
Cohesion: 0.29
Nodes (3): delete({{ $person->id }}), detail({{ $person->id }}), edit({{ $person->id }})

### Community 31 - "Public Vendor"
Cohesion: 0.33
Nodes (7): _construct(), _inherits(), _isNativeFunction(), isNativeReflectConstruct(), _setPrototypeOf(), _wrapNativeSuper(), _wrapRegExp()

### Community 32 - "Public Vendor"
Cohesion: 0.38
Nodes (7): _arrayWithHoles(), _iterableToArrayLimit(), _iterableToArrayLimitLoose(), _nonIterableRest(), _slicedToArray(), _slicedToArrayLoose(), _toArray()

### Community 33 - "Public Vendor"
Cohesion: 0.33
Nodes (7): _construct(), _inherits(), _isNativeFunction(), isNativeReflectConstruct(), _setPrototypeOf(), _wrapNativeSuper(), _wrapRegExp()

### Community 34 - "Closeimportmodal"
Cohesion: 0.33
Nodes (5): closeImportModal, downloadTemplateMustahik, finalizeImport, previewImport, $set(

### Community 35 - "Database Factories"
Cohesion: 0.47
Nodes (3): UserFactory, Illuminate\Database\Eloquent\Factories\Factory, static

### Community 36 - "Public Vendor"
Cohesion: 0.33
Nodes (6): _defineProperty(), _get(), _getPrototypeOf(), _objectSpread(), set(), _superPropBase()

### Community 37 - "App Imports"
Cohesion: 0.60
Nodes (3): MustahikImport, Maatwebsite\Excel\Concerns\ToArray, Maatwebsite\Excel\Concerns\WithHeadingRow

### Community 38 - "Deletelampiran Lamp"
Cohesion: 0.40
Nodes (4): deleteLampiran({{ $lamp->id }}), closeLampiranModal, openLampiranModal, saveLampiran

### Community 42 - "Public Vendor"
Cohesion: 0.50
Nodes (4): _arrayWithoutHoles(), _iterableToArray(), _nonIterableSpread(), _toConsumableArray()

### Community 69 - "Public Images"
Cohesion: 0.67
Nodes (3): Garuda Pancasila Emblem, BAZNAS Official Logo, BAZNAS Institutional Identity

### Community 70 - "Readme Agentic"
Cohesion: 1.00
Nodes (3): Agentic Development Workflow, Laravel Boost Package, Laravel Framework

## Knowledge Gaps
- **160 isolated node(s):** `Controller`, `$schema`, `name`, `type`, `description` (+155 more)
  These have ≤1 connection - possible missing edges or undocumented components.
- **21 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `PengajuanBantuan` connect `App Models` to `App Models`, `App Models`?**
  _High betweenness centrality (0.012) - this node is a cross-community bridge._
- **Why does `Lembaga` connect `App Models` to `App Models`, `App Models`, `App Models`, `App Models`?**
  _High betweenness centrality (0.008) - this node is a cross-community bridge._
- **Why does `Wilayah` connect `App Models` to `App Models`?**
  _High betweenness centrality (0.007) - this node is a cross-community bridge._
- **What connects `Controller`, `$schema`, `name` to the rest of the system?**
  _160 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `Public Vendor` be split into smaller, more focused modules?**
  _Cohesion score 0.04081632653061224 - nodes in this community are weakly interconnected._
- **Should `App Models` be split into smaller, more focused modules?**
  _Cohesion score 0.06471631205673758 - nodes in this community are weakly interconnected._
- **Should `Composer` be split into smaller, more focused modules?**
  _Cohesion score 0.044444444444444446 - nodes in this community are weakly interconnected._