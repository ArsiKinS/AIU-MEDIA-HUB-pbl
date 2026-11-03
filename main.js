const newsGrid = document.getElementById("newsGrid");
const searchbar = document.querySelector("#searchBar");
const crossBtn = document.querySelector("#clearSearch");

let data = []; 
let bookmarks = [];

// Fetch data from API
fetch('API/getContent.php')
    .then(response => response.json())
    .then(dbData => {
        data = dbData;
        renderItems(data); // Initial display of all items
    })
    .catch(err => console.error("Error loading data:", err));

// Function to render cards
function renderItems(itemsToDisplay) {
    newsGrid.innerHTML = ""; // Clear grid to prevent duplicates

    itemsToDisplay.forEach((item, index) => {
        const card = document.createElement("div");
        card.classList.add("news_cardsmall");

        card.innerHTML = `
            <img src="${item.MEDIA_PATH}" alt="${item.title}">
            <div class="post_info">
                <a href="#" class="category_button">${item.content_type}</a>
                <h2 class="class_title font-bold">${item.title}</h2>
                <p class="post_body text-sm">${item.body_content}</p>
                <div class="flex items-center gap-2"> 
                    <button class="read_more_btn px-3 py-1 text-white rounded">Read More</button> 
                    <i class="bookmark far fa-bookmark ml-1.5 text-xl mt-1 text-[#64b12b] cursor-pointer hover:text-[#0d1849]"></i>
                </div>
                <small>${item.created_on}</small>
            </div>
        `;

        newsGrid.appendChild(card);

        // Read More toggle
        const btn = card.querySelector(".read_more_btn");
        const postBody = card.querySelector(".post_body");
        btn.addEventListener("click", () => {
            postBody.classList.toggle("expanded");
            btn.textContent = postBody.classList.contains("expanded") ? "Read Less" : "Read More";
        });

        // Make card clickable (skip button & bookmark)
        card.addEventListener("click", (e) => {
            if (e.target.closest(".read_more_btn") || e.target.closest(".bookmark")) return;
            window.location.href = `news-template.html?id=${item.content_id}`;
        });

        // Bookmark toggle
        const bookmark = card.querySelector(".bookmark");
        bookmark.addEventListener("click", () => {
            bookmark.classList.toggle("far");
            bookmark.classList.toggle("fas");
        });
    });
}

// Search Function
searchbar.addEventListener("keyup", function(event) {
    let input = searchbar.value.toLowerCase();
    
    // Filter the data - search in title, body_content, and content_type
    let filteredData = data.filter(obj => 
        obj.title.toLowerCase().includes(input) ||
        obj.body_content.toLowerCase().includes(input) ||
        obj.content_type.toLowerCase().includes(input)
    );

    renderItems(filteredData); // Re-draw the grid with filtered items

    if (event.key === "Enter") {
        showResults();
    }
});

// Clear button
crossBtn.addEventListener("click", () => {
    console.log("Clearing search...");
    searchbar.value = "";
    renderItems(data); 
    searchbar.focus();
});

// Bookmark click handler
document.addEventListener("click", (e) => {
    if (e.target.classList.contains("bookmark")) {
        const card = e.target.closest(".news_cardsmall");
        const title = card.querySelector(".class_title").innerText;
        const image = card.querySelector("img").src;
        const date = card.querySelector("small").innerText;

        if (e.target.classList.contains("fas")) {
            bookmarks.push({ title, image, date });
        } else {
            bookmarks = bookmarks.filter(item => item.title !== title);
        }

        renderBookmarks();
    }

    // Trash button handler
    if (e.target.closest(".trash_btn")) {
        const itemdiv = e.target.closest(".flex");
        const title = itemdiv.querySelector("p").innerText;
        bookmarks = bookmarks.filter(item => item.title !== title);
        renderBookmarks();
    }
});

// Render bookmarks
function renderBookmarks() {
    const list = document.getElementById("bookmarkList");
    list.innerHTML = "";
    
    bookmarks.forEach(item => {
        const div = document.createElement("div");
        div.className = "flex gap-3 border-b pb-2 scrollbar-hide";

        div.innerHTML = `
            <img src="${item.image}" class="w-12 h-12 object-cover">
            <div>
                <p class="text-sm font-semibold">${item.title}</p>
                <small class="text-gray-500">${item.date}</small>
            </div>
            <button class="trash_btn r-0 ml-auto">
                <i class="fa-solid fa-trash-can"></i>
            </button>
        `;

        list.appendChild(div);
    });
}

// Sidebar functionality
const closebtn = document.getElementById("closeSidebar");
const sidebar = document.getElementById("sidebar");
const bookmark = document.getElementById("bookmark");

bookmark.addEventListener("click", () => {
    sidebar.classList.remove("translate-x-full");
});

closebtn.addEventListener("click", () => {
    sidebar.classList.add("translate-x-full");
});

// Profile sidebar
const close_btn = document.getElementById("p_closeSidebar");
const profilesidebar = document.getElementById("profile_sidebar");
const profileicon = document.getElementById("profile-icon");

close_btn.addEventListener("click", () => {
    console.log("hi");
    profilesidebar.classList.add("translate-x-full");
});

profileicon.addEventListener("click", () => {
    profilesidebar.classList.remove("hidden");
    console.log("hi");
    profilesidebar.classList.remove("translate-x-full");
});

// Profile editing
document.addEventListener("DOMContentLoaded", () => {
    const pencil = document.getElementById("pencil");
    const nameContainer = document.getElementById("namecontainer");
    const nameInput = document.getElementById("nameInput");
    const saveBtn = document.getElementById("saveBtn");
    const h3Name = nameContainer.querySelector("h3");

    if (pencil && nameContainer && nameInput && saveBtn && h3Name) {
        pencil.addEventListener("click", () => {
            nameInput.classList.remove("hidden");
            saveBtn.classList.remove("hidden");
            pencil.classList.add("hidden");
            nameInput.value = h3Name.textContent.trim();
            h3Name.classList.add("hidden");
        });

        saveBtn.addEventListener("click", () => {
            h3Name.textContent = nameInput.value;
            h3Name.classList.remove("hidden");
            nameInput.classList.add("hidden");
            saveBtn.classList.add("hidden");
            pencil.classList.remove("hidden");
        });
    }

    const pencil_about = document.getElementById("pencil_about");
    const aboutInput = document.getElementById("aboutInput");
    const aboutBtn = document.getElementById("aboutBtn");
    const aboutContainer = document.getElementById("aboutcontainer");
    const h4Name = aboutContainer?.querySelector("p");

    if (pencil_about && aboutInput && aboutBtn && aboutContainer && h4Name) {
        pencil_about.addEventListener("click", () => {
            aboutInput.classList.remove("hidden");
            aboutBtn.classList.remove("hidden");
            pencil_about.classList.add("hidden");
            aboutInput.value = h4Name.textContent.trim();
            h4Name.classList.add("hidden");
        });

        aboutBtn.addEventListener("click", () => {
            h4Name.textContent = aboutInput.value;
            h4Name.classList.remove("hidden");
            aboutInput.classList.add("hidden");
            aboutBtn.classList.add("hidden");
            pencil_about.classList.remove("hidden");
        });
    }

    // Session2 btn
    const btn = document.getElementById("2btn");
    if (btn) {
        btn.addEventListener("click", () => {
            console.log("hi");
            const card = btn.closest(".news_card");
            const postBody = card.querySelector(".post_body");
            postBody.classList.toggle("expanded");
            btn.textContent = postBody.classList.contains("expanded") ? "Read Less" : "Read More";
        });
    }
});

// Nav Search
const searchInput = document.getElementById('search-input');
const searchTrigger = document.getElementById('search-icon-trigger');

if (searchTrigger && searchInput) {
    searchTrigger.addEventListener('click', () => {
        searchInput.classList.toggle('search-active');
    });
}

// Scroll function
function showResults() {
    const results = document.getElementById("newsGrid");
    results.scrollIntoView({ behavior: "smooth" });
}