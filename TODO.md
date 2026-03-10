# Sidebar & Top Bar Improvements

## Task: Fix sidebar menu - hover to expand (no click required)

### Progress:
- [x] Analyze current implementation
- [x] Create improvement plan
- [x] Get user approval
- [x] Fix AppLayout.vue - Changed from click to hover
- [x] Enhance sidebar.scss - Hover-based expansion styling
- [x] Add data-title attributes for tooltips

### Changes Made:

1. **AppLayout.vue:**
   - Changed from click-to-collapse to **hover-to-expand**
   - Replaced `sidebarCollapsed` ref with `sidebarHovered` ref
   - Added `@mouseenter` and `@mouseleave` event handlers
   - Removed toggle button
   - Top bar now adjusts dynamically based on hover state

2. **sidebar.scss:**
   - Default state is now collapsed (70px)
   - Expanded state (250px) on hover
   - Tooltips appear on hover showing menu item names
   - Icons centered in collapsed mode
   - Smooth transitions for all changes

### Key Features:
- **Hover-based**: Sidebar stays collapsed (70px) by default, expands to 250px on hover
- **Top bar adjusts**: Navbar width dynamically changes from `calc(100% - 70px)` to `calc(100% - 250px)` on hover
- **Tooltips**: When collapsed, hover over icons to see the menu name
- **No click needed**: Just hover over the sidebar to expand it
- **Smooth animations**: 0.3s ease transitions on all elements

