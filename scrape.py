import requests
from bs4 import BeautifulSoup
import csv

base_url = 'https://accessiblegamesdatabase.com/?product-page='
max_pages = 14

products = []

for page in range(1, max_pages + 1):
    url = f'{base_url}{page}'
    response = requests.get(url)
    soup = BeautifulSoup(response.content, 'html.parser')

    # Find all the list of games
    product_list_items = soup.find('ul', class_='products').find_all('li')

    for item in product_list_items:
        # Get the game's name and link to more details
        heading = item.find('h2')
        if heading:
            product_name = heading.text.strip()
            product_link = item.find('a')['href']
            products.append({'name': product_name, 'link': product_link})

detailed_products = []
accessibility_features = []
for index, product in enumerate(products):
    product_url = product['link']
    product_response = requests.get(product_url)
    product_soup = BeautifulSoup(product_response.content, 'html.parser')
    
    # Extract game rating
    rating_element = product_soup.find('b', string='Industry Rating: ')
    if rating_element:
        game_rating = rating_element.find_next('a').text.strip()
    if not rating_element or game_rating == '':
        game_rating = 'N/A'

    # Extract game platforms
    platforms_element = product_soup.find('b', string='Platform: ')
    if platforms_element:
        next_sibling = platforms_element.find_next_sibling()
        game_platforms = []
        while next_sibling and next_sibling.name != 'b':
            if next_sibling.name == 'a':
                game_platforms.append(next_sibling.text.strip())
            next_sibling = next_sibling.find_next_sibling()
        game_platforms = ', '.join(game_platforms)
    if not platforms_element or game_platforms == '':
        game_platforms = 'N/A'

    # Extract game categories
    category_element = product_soup.find('b', string='Genre: ')
    if category_element:
        next_sibling = category_element.find_next_sibling()
        game_cat = []
        while next_sibling and next_sibling.name != 'b':
            if next_sibling.name == 'a':
                game_cat.append(next_sibling.text.strip())
            next_sibling = next_sibling.find_next_sibling()
        game_cat = ', '.join(game_cat)
    if not category_element or game_cat == '':
        game_cat = 'N/A'

    # Some information is not available
    detailed_products.append({
        'game_id': index + 1,
        'game_rating': game_rating,
        'gamename': product['name'],
        'game_release_date': 'N/A',
        'game_cat': game_cat,
        'game_publisher': 'N/A',
        'game_platforms': game_platforms
    })

    # Extract accessibility features
    accessibility_list = product_soup.find('div', id='accessibility-list')
    if accessibility_list:
        feature_cat = None
        for element in accessibility_list.children:
            if element.name == 'b':
                feature_cat = element.text.strip()
            elif element.name == 'ul' and feature_cat:
                for li in element.find_all('li', recursive=False):
                    feature_name = li.find('a').text.strip() if li.find('a') else li.text.strip()
                    accessibility_features.append({
                        'feature_id': len(accessibility_features) + 1,
                        'feature_name': feature_name,
                        'feature_cat': feature_cat,
                        'feature_desc': 'N/A',
                        'game_id': index + 1,
                    })
            elif element.name == 'li' and feature_cat:
                feature_name = element.find('a').text.strip() if element.find('a') else element.text.strip()
                accessibility_features.append({
                    'feature_id': len(accessibility_features) + 1,
                    'feature_name': feature_name,
                    'feature_cat': feature_cat,
                    'feature_desc': 'N/A',
                    'game_id': index + 1,
                })

# Write the detailed product information to a CSV file
csv_file = 'games_details.csv'
csv_columns = ['game_id', 'game_rating', 'gamename', 'game_release_date', 'game_cat', 'game_publisher', 'game_platforms']

with open(csv_file, 'w', newline='', encoding='utf-8') as csvfile:
    writer = csv.DictWriter(csvfile, fieldnames=csv_columns)
    writer.writeheader()
    for data in detailed_products:
        writer.writerow(data)

# Write the accessibility features to a CSV file
accessibility_csv_file = 'accessibility_features.csv'
accessibility_csv_columns = ['feature_id', 'feature_name', 'feature_cat', 'feature_desc', 'game_id']

with open(accessibility_csv_file, 'w', newline='', encoding='utf-8') as csvfile:
    writer = csv.DictWriter(csvfile, fieldnames=accessibility_csv_columns)
    writer.writeheader()
    for data in accessibility_features:
        writer.writerow(data)

print(f"Data has been written to {csv_file} and {accessibility_csv_file}")