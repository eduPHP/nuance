---
name: daisyui-development
description: Expertise in utilizing **daisyUI** components to build modern, responsive, and accessible user interfaces. This skill focuses on leveraging pre-designed component classes (e.g., `btn`, `card`, `modal`) to reduce Tailwind utility verbosity while maintaining full customization through Tailwind CSS.
---

### **Core Principles**

* **Component First:** Always prefer daisyUI component classes over raw Tailwind utility strings for standard elements.
* **Semantic HTML:** Use appropriate tags (`<button>`, `<nav>`, `<section>`) enhanced by daisyUI classes.
* **Theme Awareness:** Utilize the `data-theme` attribute and semantic color names (e.g., `primary`, `secondary`, `accent`, `ghost`).
* **Low Verbosity:** Keep the HTML clean; only add utility classes (`mt-4`, `flex-col`) when the base component needs layout adjustments.

---

### **Component Implementation Guide**

#### **1. Layout & Containers**

| Component | Class | Usage Note |
| --- | --- | --- |
| **Navbar** | `.navbar` | Use inside a header; contains `.navbar-start`, `.navbar-center`, and `.navbar-end`. |
| **Card** | `.card` | Use with `.card-body` and `.card-title`. Add `.image-full` for overlay styles. |
| **Footer** | `.footer` | Automatically creates a responsive grid layout for links. |

#### **2. Actions & Inputs**

* **Buttons:** Use `.btn`. Variants include `.btn-primary`, `.btn-outline`, `.btn-ghost`, and sizes like `.btn-sm`.
* **Inputs:** Use `.input .input-bordered`. For validation, use `.input-error` or `.input-success`.
* **Toggles:** Use `.toggle .toggle-primary`.

#### **3. Feedback & Overlays**

* **Modals:** Use the `<dialog>` element with `.modal` and `.modal-box`.
* **Alerts:** Use `.alert` with icons and text. Control state with `.alert-info`, `.alert-warning`, etc.

---

### **Theming & Colors**

Avoid hex codes. Use semantic utility classes to ensure compatibility with light/dark modes:

* **Backgrounds:** `bg-base-100` (deepest), `bg-base-200`, `bg-base-300` (shallowest).
* **Content:** `text-base-content`, `text-primary-content`.
* **Brand:** `btn-primary`, `text-secondary`, `bg-accent`.

---

### **Code Pattern Examples**

#### **Standard Card with Image**

```html
<div class="card w-96 bg-base-100 shadow-xl">
  <figure><img src="image.jpg" alt="Shoes" /></figure>
  <div class="card-body">
    <h2 class="card-title">New Release!</h2>
    <p>If a dog chews shoes whose shoes does he choose?</p>
    <div class="card-actions justify-end">
      <button class="btn btn-primary">Buy Now</button>
    </div>
  </div>
</div>

```

#### **Responsive Navbar**

```html
<div class="navbar bg-base-100">
  <div class="flex-1">
    <a class="btn btn-ghost text-xl">daisyUI</a>
  </div>
  <div class="flex-none">
    <ul class="menu menu-horizontal px-1">
      <li><a>Link</a></li>
      <li>
        <details>
          <summary>Parent</summary>
          <ul class="p-2 bg-base-100 rounded-t-none">
            <li><a>Submenu 1</a></li>
            <li><a>Submenu 2</a></li>
          </ul>
        </details>
      </li>
    </ul>
  </div>
</div>

```

---

### **Instructions for the AI Agent**

1. **Check Configuration:** Ensure `daisyui` is listed in the `plugins` array of `tailwind.config.js`.
2. **Avoid Redundancy:** Do not manually build a button using `px-4 py-2 rounded bg-blue-500` if `.btn .btn-primary` achieves the same result.
3. **Responsive Design:** Use daisyUI's responsive modifiers (e.g., `.menu-vertical lg:menu-horizontal`).
4. **Accessibility:** Ensure `aria-label` is present on icon-only buttons and `role="alert"` on notifications.
