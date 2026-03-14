const result = [
    ...document.querySelector('#user-repositories-list')
        .querySelectorAll('li[itemtype="http://schema.org/Code"]')
].map((repoEl) => {
    const nameElement = repoEl.querySelector('a[itemprop="name codeRepository"]')
    const name = nameElement.textContent.trim()
    const url = nameElement.getAttribute('href')

    const description = repoEl.querySelector('p[itemprop="description"]')?.textContent.trim() || null
    const language = repoEl.querySelector('span[itemprop="programmingLanguage"]')?.textContent.trim() || null
    
    const stars = parseInt(repoEl.querySelector('a[href*="stargazers"]')?.textContent.trim() || 0)
    const forks = parseInt(repoEl.querySelector('a[href*="forks"]')?.textContent.trim() || 0)

    const lastUpdatedText = repoEl.querySelector('relative-time').getAttribute('datetime')

    const status = repoEl.querySelector('.Label').textContent.trim()

    return {
        name: name,
        fullName: url.substring(1),
        url: `https://github.com${url}`,
        description: description,
        language: language,
        stars: stars,
        forks: forks,
        lastUpdatedText: lastUpdatedText,
        status: status,
    }
})

console.log(result)