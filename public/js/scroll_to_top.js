// Получение объекта кнопки
buttonScrollToTop = document.getElementById("button_scroll_to_top");

// Вызов функции проверки при каждом прокручивании страницы
window.onscroll = function()
{
    scrollFunction()
};

// Функция проверки - если страница прокручена ниже 20 пикселей, то кнопка видна, иначе - нет
function scrollFunction()
{
    if (document.body.scrollTop > 20 || document.documentElement.scrollTop > 20)
    {
        buttonScrollToTop.style.display = "block";
    }
    else
    {
        buttonScrollToTop.style.display = "none";
    }
}

// Функция прокручивания при нажатии на кнопку
function scrollToTopFunction()
{
    document.body.scrollTop = 0; // Для Safari
    document.documentElement.scrollTop = 0; // Для Chrome, Firefox, IE и Opera
}